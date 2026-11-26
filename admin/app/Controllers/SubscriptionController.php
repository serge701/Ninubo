<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Settings;
use Crm\Models\Activity;
use Crm\Models\Client;
use Crm\Models\Invoice;
use Crm\Models\Subscription;

/** Recurring services (care plans, yearly renewals) on a client. */
final class SubscriptionController extends Controller
{
    /** Creates (or opens) the draft invoice for the service's next period. */
    public function invoice(string $id): void
    {
        $sub = Subscription::find((int)$id) ?? $this->notFound();
        if ($sub['status'] !== 'active') {
            $this->redirect('/clients/' . $sub['client_id']);
        }
        if ($draft = Subscription::pendingDraftId((int)$sub['id'])) {
            $this->success('A draft for this period already exists.');
            $this->redirect('/invoices/' . $draft);
        }
        $client = Client::find((int)$sub['client_id']);
        $issue = date('Y-m-d');
        $due = max($sub['next_invoice_date'], date('Y-m-d', strtotime($issue . ' +' . (int)Settings::get('invoice_due_days') . ' days')));
        $userId = (int)$this->user()['id'];
        $invoiceId = Invoice::saveDraft(null, $client, [
            'issue_date' => $issue,
            'due_date' => $due,
            'payment_terms' => 'full',
            'notes' => '',
            'internal_notes' => '',
        ], [Subscription::nextLine($sub)], $userId);
        Activity::log((int)$client['id'], $userId, 'Invoice draft created for "' . $sub['description'] . '" (' . Subscription::periodLabel($sub['next_invoice_date'], $sub['billing']) . ').');
        $this->success('Draft created for the next period. Review it and issue it.');
        $this->redirect('/invoices/' . $invoiceId);
    }

    public function update(string $id): void
    {
        $sub = Subscription::find((int)$id) ?? $this->notFound();
        $price = str_replace([',', '$', ' '], '', (string)$this->input('unit_price'));
        $next = (string)$this->input('next_invoice_date');
        $d = \DateTime::createFromFormat('Y-m-d', $next);
        if (!is_numeric($price) || (float)$price < 0 || !$d || $d->format('Y-m-d') !== $next) {
            \Crm\Core\Session::flash('danger', 'Enter a valid price and next invoice date.');
            $this->redirect('/clients/' . $sub['client_id'] . '#recurring');
        }
        Subscription::update((int)$sub['id'], ['unit_price' => round((float)$price, 2), 'next_invoice_date' => $next]);
        Activity::log((int)$sub['client_id'], (int)$this->user()['id'], '"' . $sub['description'] . '" updated: ' . money($price) . ', next invoice ' . fmt_date($next) . '.');
        $this->success('Recurring service updated.');
        $this->redirect('/clients/' . $sub['client_id'] . '#recurring');
    }

    public function cancel(string $id): void
    {
        $sub = Subscription::find((int)$id) ?? $this->notFound();
        Subscription::update((int)$sub['id'], ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')]);
        Activity::log((int)$sub['client_id'], (int)$this->user()['id'], 'Recurring service "' . $sub['description'] . '" cancelled.');
        $this->success('Recurring service cancelled. No more invoices will be suggested for it.');
        $this->redirect('/clients/' . $sub['client_id'] . '#recurring');
    }
}
