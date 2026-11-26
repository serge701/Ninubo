<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Models\Activity;
use Crm\Models\Brief;
use Crm\Models\Client;
use Crm\Models\Contract;
use Crm\Models\Invoice;
use Crm\Models\Package;
use Crm\Models\Subscription;
use Crm\Models\User;

/** Clients are the starting point of every sale: briefs, invoices and contracts are created from a client. */
final class ClientController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => (string)$this->input('q'),
            'status' => (string)$this->input('status'),
            'assigned' => (string)$this->input('assigned'),
        ];
        $page = max(1, (int)$this->input('page', 1));
        $result = Client::search($filters, $page);

        $this->view('clients/index', [
            'title' => 'Clients',
            'clients' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int)ceil($result['total'] / Client::PER_PAGE)),
            'filters' => $filters,
            'counts' => Client::counts(),
            'users' => $this->salesUsers(),
        ]);
    }

    public function create(): void
    {
        $this->view('clients/form', [
            'title' => 'New client',
            'client' => null,
            'users' => $this->salesUsers(),
        ]);
    }

    public function store(): void
    {
        $data = Client::fromInput($_POST);
        if (!isset($_POST['assigned_to'])) {
            $data['assigned_to'] = (int)$this->user()['id'];
        }
        $errors = Client::validate($data);
        if ($errors) {
            $this->back('/clients/new', $errors, $_POST);
        }
        $userId = (int)$this->user()['id'];
        $id = Client::create($data, $userId);
        Activity::log($id, $userId, 'Client created as ' . Client::STATUSES[$data['status']] . '.');
        $this->success('Client created. From here you can send a website brief, and later create invoices and contracts.');
        $this->redirect('/clients/' . $id);
    }

    public function show(string $id): void
    {
        $client = Client::find((int)$id) ?? $this->notFound();
        $this->view('clients/show', [
            'title' => Client::displayName($client),
            'pretitle' => 'Client',
            'client' => $client,
            'activities' => Activity::forClient((int)$client['id']),
            'briefs' => Brief::forClient((int)$client['id']),
            'briefPackages' => Package::withBrief(),
            'invoices' => Invoice::forClient((int)$client['id']),
            'subscriptions' => Subscription::forClient((int)$client['id']),
            'contracts' => Contract::forClient((int)$client['id']),
        ]);
    }

    public function edit(string $id): void
    {
        $client = Client::find((int)$id) ?? $this->notFound();
        $this->view('clients/form', [
            'title' => 'Edit client',
            'pretitle' => Client::displayName($client),
            'client' => $client,
            'users' => $this->salesUsers(),
        ]);
    }

    public function update(string $id): void
    {
        $client = Client::find((int)$id) ?? $this->notFound();
        $data = Client::fromInput($_POST);
        $errors = Client::validate($data);
        if ($errors) {
            $this->back('/clients/' . $id . '/edit', $errors, $_POST);
        }
        Client::update((int)$client['id'], $data);

        // Timeline entries for the changes that matter in a sales pipeline.
        $userId = (int)$this->user()['id'];
        if ($data['status'] !== $client['status']) {
            Activity::log((int)$client['id'], $userId, 'Status changed from ' . Client::STATUSES[$client['status']] . ' to ' . Client::STATUSES[$data['status']] . '.');
        }
        if ((int)$data['assigned_to'] !== (int)$client['assigned_to']) {
            $newOwner = $data['assigned_to'] ? (User::find($data['assigned_to'])['name'] ?? '?') : 'nobody';
            Activity::log((int)$client['id'], $userId, 'Assigned to ' . $newOwner . '.');
        }

        $this->success('Client updated.');
        $this->redirect('/clients/' . $id);
    }

    public function destroy(string $id): void
    {
        $client = Client::find((int)$id) ?? $this->notFound();
        if (Invoice::countForClient((int)$client['id']) > 0 || Contract::countForClient((int)$client['id']) > 0) {
            \Crm\Core\Session::flash('danger', 'This client has invoices or contracts, so it cannot be deleted (those records must be kept). Mark the client as Inactive instead.');
            $this->redirect('/clients/' . $client['id'] . '/edit');
        }
        Client::delete((int)$client['id']);
        $this->success('Client "' . Client::displayName($client) . '" deleted. Their website briefs were kept.');
        $this->redirect('/clients');
    }

    /** Active users for the "assigned to" select. */
    private function salesUsers(): array
    {
        return array_values(array_filter(User::all(), fn($u) => (int)$u['is_active'] === 1));
    }
}
