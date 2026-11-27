<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\Activity;
use Crm\Models\Brief;
use Crm\Models\Client;
use Crm\Models\Package;

final class BriefController extends Controller
{
    public function index(): void
    {
        $status = (string)$this->input('status');
        $this->view('briefs/index', [
            'title' => 'Website briefs',
            'briefs' => Brief::all($status !== '' ? $status : null),
            'counts' => Brief::counts(),
            'status' => $status,
            'clientOptions' => Client::options(),
            'briefPackages' => Package::withBrief(),
        ]);
    }

    /** Creates a brief link for a client + package (from the client page or the briefs list). */
    public function store(): void
    {
        $client = Client::find((int)$this->input('client_id', 0));
        $package = Package::find((int)$this->input('package_id', 0));
        $from = (string)$this->input('from') === 'client' && $client ? '/clients/' . $client['id'] : '/briefs';

        if (!$client) {
            Session::flash('danger', 'Choose the client first. If they are new, create the client and send the brief from their page.');
            $this->redirect($from);
        }
        if (!$package || $package['brief_profile'] === null || !(int)$package['is_active']) {
            Session::flash('danger', 'Choose a website package — its questions are what the client will see.');
            $this->redirect($from);
        }

        $userId = (int)$this->user()['id'];
        $id = Brief::create($client, $package, $userId);
        Activity::log((int)$client['id'], $userId, 'Website brief link created for "' . $package['name'] . '".');
        Session::flash('new_brief_link', Brief::publicLink(Brief::find($id)));
        $this->success('Brief link created. Copy it and send it to the client.');
        $this->redirect('/briefs/' . $id);
    }

    public function show(string $id): void
    {
        $brief = Brief::find((int)$id) ?? $this->notFound();
        $data = json_decode((string)$brief['data'], true);
        $this->view('briefs/show', [
            'title' => $brief['client_label'],
            'pretitle' => 'Website brief',
            'brief' => $brief,
            'data' => is_array($data) ? $data : [],
            'schema' => ninubo_brief_schema('en', $brief['brief_profile']),
            'link' => Brief::publicLink($brief),
        ]);
    }

    /** A submitted brief goes back to "in progress" so the client can correct it with the same link. */
    public function reopen(string $id): void
    {
        $brief = Brief::find((int)$id) ?? $this->notFound();
        if ($brief['status'] === 'completado') {
            Brief::reopen((int)$brief['id']);
            if ($brief['client_id']) {
                Activity::log((int)$brief['client_id'], (int)$this->user()['id'], 'Website brief "' . ($brief['package_name'] ?: 'Website brief') . '" reopened so the client can edit it.');
            }
            $this->success('Brief reopened. The client can edit it again with the same link.');
        }
        $this->redirect('/briefs/' . $brief['id']);
    }

    public function destroy(string $id): void
    {
        $brief = Brief::find((int)$id) ?? $this->notFound();
        Brief::delete((int)$id);
        if ($brief['client_id']) {
            $name = $brief['package_name'] ?: 'Website brief';
            $state = strtolower(Brief::STATUSES[$brief['status']] ?? $brief['status']);
            Activity::log((int)$brief['client_id'], (int)$this->user()['id'], 'Website brief "' . $name . '" deleted (' . $state . ').');
        }
        $this->success('Brief "' . $brief['client_label'] . '" deleted.');
        $toList = (string)$this->input('from') === 'list' || !$brief['client_id'];
        $this->redirect($toList ? '/briefs' : '/clients/' . $brief['client_id']);
    }

    /** /admin/brief-ver.php?id=N (link format in older notification emails). */
    public function legacy(): void
    {
        $id = (int)$this->input('id', 0);
        $this->redirect($id > 0 ? '/briefs/' . $id : '/briefs');
    }
}
