<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\App;
use Crm\Core\Controller;
use Crm\Core\Pdf;
use Crm\Core\Session;
use Crm\Core\Settings;
use Crm\Models\Activity;
use Crm\Models\Client;
use Crm\Models\Invoice;
use Crm\Models\Package;

/** Invoices are always created from a client (client page → New → Invoice). */
final class InvoiceController extends Controller
{
    public function index(): void
    {
        $filters = ['status' => (string)$this->input('status'), 'q' => (string)$this->input('q')];
        $this->view('invoices/index', [
            'title' => 'Invoices',
            'invoices' => Invoice::search($filters),
            'filters' => $filters,
            'summary' => Invoice::summary(),
        ]);
    }

    public function create(string $clientId): void
    {
        $client = Client::find((int)$clientId) ?? $this->notFound();
        $today = date('Y-m-d');
        $this->view('invoices/form', [
            'title' => 'New invoice',
            'pretitle' => Client::displayName($client),
            'client' => $client,
            'invoice' => null,
            'header' => [
                'issue_date' => $today,
                'due_date' => date('Y-m-d', strtotime($today . ' +' . (int)Settings::get('invoice_due_days') . ' days')),
                'payment_terms' => '',
                'notes' => '',
                'internal_notes' => '',
            ],
            'items' => [],
            'catalog' => Package::all(true),
        ]);
    }

    public function store(): void
    {
        $client = Client::find((int)$this->input('client_id', 0)) ?? $this->notFound();
        [$header, $items, $errors] = $this->formInput();
        if ($errors) {
            $this->backToForm('/clients/' . $client['id'] . '/invoices/new', $errors);
        }
        $userId = (int)$this->user()['id'];
        $id = Invoice::saveDraft(null, $client, $header, $items, $userId);
        $inv = Invoice::find($id);
        Activity::log((int)$client['id'], $userId, 'Invoice draft created (' . money($inv['total']) . ').');
        $this->success('Draft saved. Review it and click "Issue invoice" to assign its number.');
        $this->redirect('/invoices/' . $id);
    }

    public function show(string $id): void
    {
        $inv = Invoice::find((int)$id) ?? $this->notFound();
        $this->view('invoices/show', [
            'title' => Invoice::displayNumber($inv),
            'pretitle' => 'Invoice · ' . Client::displayName($inv),
            'inv' => $inv,
            'items' => Invoice::items((int)$inv['id']),
            'payments' => Invoice::payments((int)$inv['id']),
            'schedule' => Invoice::schedule($inv),
        ]);
    }

    public function edit(string $id): void
    {
        $inv = $this->draftOr404($id);
        $client = Client::find((int)$inv['client_id']);
        $this->view('invoices/form', [
            'title' => 'Edit ' . Invoice::displayNumber($inv),
            'pretitle' => Client::displayName($client),
            'client' => $client,
            'invoice' => $inv,
            'header' => $inv,
            'items' => Invoice::items((int)$inv['id']),
            'catalog' => Package::all(true),
        ]);
    }

    public function update(string $id): void
    {
        $inv = $this->draftOr404($id);
        [$header, $items, $errors] = $this->formInput();
        if ($errors) {
            $this->backToForm('/invoices/' . $inv['id'] . '/edit', $errors);
        }
        Invoice::saveDraft((int)$inv['id'], Client::find((int)$inv['client_id']), $header, $items, (int)$this->user()['id']);
        $this->success('Draft updated.');
        $this->redirect('/invoices/' . $inv['id']);
    }

    public function issue(string $id): void
    {
        $inv = $this->draftOr404($id);
        if ((float)$inv['total'] <= 0) {
            Session::flash('danger', 'An invoice must have a total greater than $0 before it is issued.');
            $this->redirect('/invoices/' . $inv['id']);
        }
        $number = Invoice::issue($inv, (int)$this->user()['id']);
        Activity::log((int)$inv['client_id'], (int)$this->user()['id'], "Invoice {$number} issued for " . money($inv['total']) . '.');
        $this->success("Invoice {$number} issued. Download the PDF to send it to the client.");
        $this->redirect('/invoices/' . $inv['id']);
    }

    public function pdf(string $id): void
    {
        $inv = Invoice::find((int)$id) ?? $this->notFound();
        $client = Client::find((int)$inv['client_id']);
        Pdf::stream('invoices/pdf', [
            'inv' => $inv,
            'client' => $client,
            'items' => Invoice::items((int)$inv['id']),
            'payments' => Invoice::payments((int)$inv['id']),
            'schedule' => Invoice::schedule($inv),
            's' => Settings::all(),
            'logo' => Pdf::logoDataUri(),
        ], 'Invoice ' . Invoice::displayNumber($inv) . '.pdf', (bool)$this->input('inline', false));
    }

    public function recordPayment(string $id): void
    {
        $inv = Invoice::find((int)$id) ?? $this->notFound();
        if (!in_array($inv['status'], ['sent', 'partial'], true)) {
            Session::flash('danger', 'Payments can only be recorded on issued invoices with a balance.');
            $this->redirect('/invoices/' . $inv['id']);
        }
        $amount = round((float)str_replace([',', '$', ' '], '', (string)$this->input('amount')), 2);
        $paidOn = (string)$this->input('paid_on');
        $method = (string)$this->input('method');
        $balance = Invoice::balance($inv);

        $error = null;
        if ($amount <= 0) {
            $error = 'Enter the amount received.';
        } elseif ($amount > $balance + 0.005) {
            $error = 'The amount is more than the balance due (' . money($balance) . ').';
        } elseif (!$this->validDate($paidOn) || $paidOn > date('Y-m-d')) {
            $error = 'Enter a valid payment date (not in the future).';
        }
        if ($error) {
            Session::flash('danger', $error);
            $this->redirect('/invoices/' . $inv['id']);
        }

        $userId = (int)$this->user()['id'];
        Invoice::recordPayment($inv, [
            'paid_on' => $paidOn,
            'amount' => $amount,
            'method' => isset(Invoice::PAYMENT_METHODS[$method]) ? $method : 'other',
            'reference' => mb_substr((string)$this->input('reference'), 0, 120),
            'notes' => mb_substr((string)$this->input('notes'), 0, 255),
        ], $userId);

        $client = Client::find((int)$inv['client_id']);
        Activity::log((int)$inv['client_id'], $userId, 'Payment of ' . money($amount) . ' received for invoice ' . $inv['number'] . ' (' . (Invoice::PAYMENT_METHODS[$method] ?? 'Other') . ').');
        if ($client['status'] === 'lead') {
            Client::update((int)$client['id'], ['status' => 'active']);
            Activity::log((int)$client['id'], $userId, 'Status changed from Lead to Active (first payment received).');
        }
        $this->success('Payment of ' . money($amount) . ' recorded.');
        $this->redirect('/invoices/' . $inv['id']);
    }

    public function deletePayment(string $paymentId): void
    {
        $invoiceId = Invoice::deletePayment((int)$paymentId) ?? $this->notFound();
        $inv = Invoice::find($invoiceId);
        Activity::log((int)$inv['client_id'], (int)$this->user()['id'], 'A payment on invoice ' . $inv['number'] . ' was deleted.');
        $this->success('Payment deleted and balance updated.');
        $this->redirect('/invoices/' . $invoiceId);
    }

    public function void(string $id): void
    {
        $inv = Invoice::find((int)$id) ?? $this->notFound();
        if ($inv['status'] === 'draft' || $inv['status'] === 'void') {
            App::abort(400, 'Cannot void', 'Only issued invoices can be voided. Drafts can simply be deleted.');
        }
        $reason = (string)$this->input('void_reason');
        if (mb_strlen($reason) < 3) {
            Session::flash('danger', 'Write the reason for voiding the invoice.');
            $this->redirect('/invoices/' . $inv['id']);
        }
        $changed = Invoice::void((int)$inv['id'], $reason);
        $extra = $changed ? ' Recurring services updated: ' . implode('; ', $changed) . '.' : '';
        Activity::log((int)$inv['client_id'], (int)$this->user()['id'], 'Invoice ' . $inv['number'] . ' voided: ' . $reason . '.' . $extra);
        $this->success('Invoice ' . $inv['number'] . ' voided. Its number is kept and will not be reused.' . $extra);
        $this->redirect('/invoices/' . $inv['id']);
    }

    public function destroy(string $id): void
    {
        $inv = $this->draftOr404($id);
        Invoice::deleteDraft((int)$inv['id']);
        Activity::log((int)$inv['client_id'], (int)$this->user()['id'], 'Invoice draft deleted.');
        $this->success('Draft deleted.');
        $this->redirect('/clients/' . $inv['client_id']);
    }

    // ---------------------------------------------------------------- helpers

    private function draftOr404(string $id): array
    {
        $inv = Invoice::find((int)$id) ?? $this->notFound();
        if ($inv['status'] !== 'draft') {
            Session::flash('warning', 'Issued invoices cannot be edited or deleted. Void it and create a new one if needed.');
            $this->redirect('/invoices/' . $inv['id']);
        }
        return $inv;
    }

    /** @return array{0: array, 1: array, 2: array} header, items, errors */
    private function formInput(): array
    {
        $parsed = Invoice::itemsFromInput(is_array($_POST['items'] ?? null) ? $_POST['items'] : []);
        $errors = $parsed['errors'];

        $issue = (string)$this->input('issue_date');
        $due = (string)$this->input('due_date');
        if (!$this->validDate($issue)) {
            $errors[] = 'Enter a valid issue date.';
        }
        if (!$this->validDate($due) || ($this->validDate($issue) && $due < $issue)) {
            $errors[] = 'The due date must be on or after the issue date.';
        }

        $hasOneTime = (bool)array_filter($parsed['items'], fn($i) => $i['billing'] === 'one_time');
        $terms = (string)$this->input('payment_terms');
        if (!isset(Invoice::TERMS[$terms])) {
            $client = Client::find((int)$this->input('client_id', 0));
            $preview = Invoice::computeTotals($parsed['items'], (bool)($client['tax_exempt'] ?? 0), (float)Settings::get('sales_tax_rate'), (float)Settings::get('sales_tax_taxable_pct'));
            $terms = Invoice::suggestedTerms($preview['total'], $hasOneTime);
        }

        $header = [
            'issue_date' => $issue,
            'due_date' => $due,
            'payment_terms' => $terms,
            'notes' => (string)$this->input('notes'),
            'internal_notes' => (string)$this->input('internal_notes'),
        ];
        return [$header, $parsed['items'], $errors];
    }

    private function backToForm(string $path, array $errors): never
    {
        Session::flash('danger', implode(' ', $errors));
        Session::flash('invoice_form', $_POST);
        $this->redirect($path);
    }

    private function validDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
