<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\App;
use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\Activity;
use Crm\Models\Client;

final class ActivityController extends Controller
{
    public function store(string $clientId): void
    {
        $client = Client::find((int)$clientId) ?? $this->notFound();
        $type = (string)$this->input('type', 'note');
        $body = (string)$this->input('body');
        $due = (string)$this->input('due_at');

        if (!isset(Activity::TYPES[$type]) || $type === 'system') {
            $type = 'note';
        }
        $dueAt = null;
        if ($type === 'task' && $due !== '') {
            $ts = strtotime($due);
            $dueAt = $ts ? date('Y-m-d H:i:s', $ts) : null;
        }
        if (mb_strlen($body) < 2) {
            Session::flash('danger', 'Write something before saving the activity.');
            Session::flash('old', ['type' => $type, 'body' => $body, 'due_at' => $due]);
            $this->redirect('/clients/' . $client['id'] . '#activity');
        }

        Activity::create((int)$client['id'], (int)$this->user()['id'], $type, $body, $dueAt);
        $this->success(Activity::TYPES[$type][0] . ' saved.');
        $this->redirect('/clients/' . $client['id'] . '#activity');
    }

    /** Marks a follow-up done / not done. */
    public function toggle(string $id): void
    {
        $activity = Activity::find((int)$id) ?? $this->notFound();
        Activity::setDone((int)$activity['id'], $activity['done_at'] === null);
        $back = (string)$this->input('back');
        $this->redirect($back === 'dashboard' ? '/' : '/clients/' . $activity['client_id'] . '#activity');
    }

    public function destroy(string $id): void
    {
        $activity = Activity::find((int)$id) ?? $this->notFound();
        $me = $this->user();
        if ($activity['type'] === 'system' || ((int)$activity['user_id'] !== (int)$me['id'] && $me['role'] !== 'admin')) {
            App::abort(403, 'Not allowed', 'You can only delete activities you wrote.');
        }
        Activity::delete((int)$activity['id']);
        $this->success('Activity deleted.');
        $this->redirect('/clients/' . $activity['client_id'] . '#activity');
    }
}
