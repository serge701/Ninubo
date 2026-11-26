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
use Crm\Models\Contract;

/** Contracts are created from a client, based on one of the client's issued invoices. */
final class ContractController extends Controller
{
    private const UPLOAD_TYPES = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
    private const UPLOAD_MAX = 10 * 1024 * 1024;

    public function index(): void
    {
        $status = (string)$this->input('status');
        $this->view('contracts/index', [
            'title' => 'Contracts',
            'contracts' => Contract::search($status),
            'counts' => Contract::counts(),
            'status' => $status,
        ]);
    }

    public function create(string $clientId): void
    {
        $client = Client::find((int)$clientId) ?? $this->notFound();
        $this->view('contracts/form', [
            'title' => 'New contract',
            'pretitle' => Client::displayName($client),
            'client' => $client,
            'contract' => null,
            'invoices' => Contract::invoiceOptions((int)$client['id']),
            'preselect' => (int)$this->input('invoice', 0),
        ]);
    }

    public function store(): void
    {
        $client = Client::find((int)$this->input('client_id', 0)) ?? $this->notFound();
        [$data, $errors] = $this->formInput($client);
        if ($errors) {
            $this->back('/clients/' . $client['id'] . '/contracts/new', $errors, $_POST);
        }
        $userId = (int)$this->user()['id'];
        $id = Contract::create($data + ['client_id' => (int)$client['id']], $userId);
        Contract::logEvent($id, 'created', '', $userId);
        Activity::log((int)$client['id'], $userId, 'Contract draft created.');
        $this->success('Contract draft saved. Review the text and click "Finalize & get signing link".');
        $this->redirect('/contracts/' . $id);
    }

    public function show(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        $this->view('contracts/show', [
            'title' => Contract::displayNumber($k),
            'pretitle' => 'Contract · ' . Client::displayName($k),
            'k' => $k,
            'body' => $k['status'] === 'draft' ? Contract::renderBody($k) : $k['body_html'],
            'events' => Contract::events((int)$k['id']),
            'link' => $k['token'] ? Contract::signLink($k) : null,
            's' => Settings::all(),
        ]);
    }

    public function edit(string $id): void
    {
        $k = $this->draftOr404($id);
        $client = Client::find((int)$k['client_id']);
        $this->view('contracts/form', [
            'title' => 'Edit ' . Contract::displayNumber($k),
            'pretitle' => Client::displayName($client),
            'client' => $client,
            'contract' => $k,
            'invoices' => Contract::invoiceOptions((int)$client['id']),
            'preselect' => (int)$k['invoice_id'],
        ]);
    }

    public function update(string $id): void
    {
        $k = $this->draftOr404($id);
        $client = Client::find((int)$k['client_id']);
        [$data, $errors] = $this->formInput($client);
        if ($errors) {
            $this->back('/contracts/' . $k['id'] . '/edit', $errors, $_POST);
        }
        Contract::update((int)$k['id'], $data);
        Contract::logEvent((int)$k['id'], 'updated', '', (int)$this->user()['id']);
        $this->success('Draft updated.');
        $this->redirect('/contracts/' . $k['id']);
    }

    public function finalize(string $id): void
    {
        $k = $this->draftOr404($id);
        if (!$k['invoice_id']) {
            Session::flash('danger', 'Choose the related invoice before finalizing.');
            $this->redirect('/contracts/' . $k['id'] . '/edit');
        }
        $userId = (int)$this->user()['id'];
        $k = Contract::finalize($k, $userId);
        Activity::log((int)$k['client_id'], $userId, 'Contract ' . $k['number'] . ' finalized; signing link created.');
        Session::flash('new_sign_link', Contract::signLink($k));
        $this->success('Contract ' . $k['number'] . ' is ready. Send the signing link to the client, or download the PDF to sign by hand.');
        $this->redirect('/contracts/' . $k['id']);
    }

    public function pdf(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        $inline = (bool)$this->input('inline', false);
        if (!$inline && $k['status'] !== 'draft') {
            Contract::logEvent((int)$k['id'], 'pdf_downloaded', 'From the CRM', (int)$this->user()['id']);
        }
        self::streamPdf($k, $inline);
    }

    /** Shared with the public signing page (sign.php). */
    public static function streamPdf(array $k, bool $inline): never
    {
        $body = $k['status'] === 'draft' ? Contract::renderBody($k) : $k['body_html'];
        Pdf::stream('contracts/pdf', [
            'k' => $k,
            'body' => $body,
            'events' => Contract::events((int)$k['id']),
            's' => Settings::all(),
            'logo' => Pdf::logoDataUri(),
        ], 'Agreement ' . Contract::displayNumber($k) . '.pdf', $inline);
    }

    /** Provider countersignature (administrators), after the client has signed electronically. */
    public function countersign(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        if ($k['status'] !== 'client_signed') {
            Session::flash('danger', 'The contract can be countersigned once the client has signed it.');
            $this->redirect('/contracts/' . $k['id']);
        }
        $signature = Contract::cleanSignature((string)($_POST['signature'] ?? ''));
        $name = trim((string)$this->input('signer_name'));
        $title = trim((string)$this->input('signer_title'));
        if (!$signature || mb_strlen($name) < 3) {
            Session::flash('danger', 'Draw the signature and write the signer\'s full name.');
            $this->redirect('/contracts/' . $k['id']);
        }
        $userId = (int)$this->user()['id'];
        Contract::update((int)$k['id'], [
            'status' => 'signed',
            'company_signature' => $signature,
            'company_signer_name' => mb_substr($name, 0, 150),
            'company_signer_title' => mb_substr($title, 0, 120),
            'company_signed_at' => date('Y-m-d H:i:s'),
            'company_signed_by' => $userId,
        ]);
        Contract::logEvent((int)$k['id'], 'countersigned', "Signed by {$name}" . ($title !== '' ? ", {$title}" : ''), $userId);
        Activity::log((int)$k['client_id'], $userId, 'Contract ' . $k['number'] . ' countersigned — fully signed.');
        $this->success('Contract countersigned. It is now fully signed; download the final PDF for your records and the client.');
        $this->redirect('/contracts/' . $k['id']);
    }

    /** Pen-and-paper path: upload the scan of the contract signed by both parties. */
    public function uploadSigned(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        if (!in_array($k['status'], ['sent', 'client_signed', 'signed'], true)) {
            Session::flash('danger', 'Finalize the contract before uploading a signed copy.');
            $this->redirect('/contracts/' . $k['id']);
        }
        $file = $_FILES['signed_file'] ?? null;
        $type = ($file && $file['error'] === UPLOAD_ERR_OK) ? (string)(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !isset(self::UPLOAD_TYPES[$type]) || $file['size'] > self::UPLOAD_MAX) {
            Session::flash('danger', 'Upload the signed contract as a PDF, JPG or PNG up to 10 MB.');
            $this->redirect('/contracts/' . $k['id']);
        }
        $name = strtolower(preg_replace('/[^A-Za-z0-9-]/', '', (string)$k['number'])) . '-signed-' . bin2hex(random_bytes(6)) . '.' . self::UPLOAD_TYPES[$type];
        if (!move_uploaded_file($file['tmp_name'], Contract::storageDir() . '/' . $name)) {
            Session::flash('danger', 'The file could not be saved.');
            $this->redirect('/contracts/' . $k['id']);
        }
        if ($k['signed_file']) {
            @unlink(Contract::storageDir() . '/' . basename($k['signed_file']));
        }
        $userId = (int)$this->user()['id'];
        Contract::update((int)$k['id'], ['status' => 'signed', 'signed_file' => $name, 'signed_file_at' => date('Y-m-d H:i:s')]);
        Contract::logEvent((int)$k['id'], 'signed_copy_uploaded', (string)$file['name'], $userId);
        Activity::log((int)$k['client_id'], $userId, 'Pen-signed copy of contract ' . $k['number'] . ' uploaded — fully signed.');
        $this->success('Signed copy uploaded. The contract is marked as fully signed.');
        $this->redirect('/contracts/' . $k['id']);
    }

    public function signedFile(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        $path = Contract::storageDir() . '/' . basename((string)$k['signed_file']);
        if (!$k['signed_file'] || !is_file($path)) {
            $this->notFound();
        }
        header('Content-Type: ' . (new \finfo(FILEINFO_MIME_TYPE))->file($path));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function cancel(string $id): void
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        if (in_array($k['status'], ['draft', 'cancelled'], true)) {
            App::abort(400, 'Cannot cancel', 'Drafts can simply be deleted.');
        }
        $reason = trim((string)$this->input('cancel_reason'));
        if (mb_strlen($reason) < 3) {
            Session::flash('danger', 'Write the reason for cancelling the contract.');
            $this->redirect('/contracts/' . $k['id']);
        }
        Contract::update((int)$k['id'], ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s'), 'cancel_reason' => mb_substr($reason, 0, 255)]);
        Contract::logEvent((int)$k['id'], 'cancelled', $reason, (int)$this->user()['id']);
        Activity::log((int)$k['client_id'], (int)$this->user()['id'], 'Contract ' . $k['number'] . ' cancelled: ' . $reason);
        $this->success('Contract cancelled. The signing link no longer works.');
        $this->redirect('/contracts/' . $k['id']);
    }

    public function destroy(string $id): void
    {
        $k = $this->draftOr404($id);
        \Crm\Core\Database::run("DELETE FROM contracts WHERE id = ? AND status = 'draft'", [(int)$k['id']]);
        Activity::log((int)$k['client_id'], (int)$this->user()['id'], 'Contract draft deleted.');
        $this->success('Draft deleted.');
        $this->redirect('/clients/' . $k['client_id']);
    }

    // ---------------------------------------------------------------- helpers

    private function draftOr404(string $id): array
    {
        $k = Contract::find((int)$id) ?? $this->notFound();
        if ($k['status'] !== 'draft') {
            Session::flash('warning', 'A finalized contract cannot be edited. Cancel it and create a new one if the terms change.');
            $this->redirect('/contracts/' . $k['id']);
        }
        return $k;
    }

    /** @return array{0: array, 1: array} */
    private function formInput(array $client): array
    {
        $errors = [];
        $invoiceId = (int)$this->input('invoice_id', 0);
        $valid = array_column(Contract::invoiceOptions((int)$client['id']), 'id');
        if (!in_array($invoiceId, array_map('intval', $valid), true)) {
            $errors['invoice_id'] = 'Choose one of the client\'s issued invoices.';
        }
        $date = (string)$this->input('effective_date');
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            $errors['effective_date'] = 'Enter a valid date.';
        }
        $name = (string)$this->input('client_signer_name');
        if (mb_strlen($name) < 3) {
            $errors['client_signer_name'] = 'Enter the full name of the person who signs for the client.';
        }
        $email = (string)$this->input('client_signer_email');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['client_signer_email'] = 'Enter a valid email address.';
        }
        return [[
            'invoice_id' => $invoiceId ?: null,
            'effective_date' => $date,
            'client_signer_name' => mb_substr($name, 0, 150),
            'client_signer_title' => mb_substr((string)$this->input('client_signer_title'), 0, 120),
            'client_signer_email' => mb_substr($email, 0, 190),
            'special_terms' => (string)$this->input('special_terms'),
        ], $errors];
    }
}
