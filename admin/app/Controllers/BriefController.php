<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\Brief;

require_once dirname(__DIR__, 3) . '/includes/brief-schema.php';
require_once dirname(__DIR__, 3) . '/includes/brief-helpers.php';

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
        ]);
    }

    public function store(): void
    {
        $label = (string)$this->input('client_label');
        if (mb_strlen($label) < 2) {
            $this->back('/briefs', ['client_label' => 'Enter a name to identify the client.'], $_POST);
        }
        $id = Brief::create(mb_substr($label, 0, 190), (int)$this->user()['id']);
        Session::flash('new_brief_link', Brief::publicLink(Brief::find($id)));
        $this->success('Link created. Copy it and send it to the client.');
        $this->redirect('/briefs/' . $id);
    }

    public function show(string $id): void
    {
        $brief = Brief::find((int)$id);
        if (!$brief) {
            $this->notFound();
        }
        $data = json_decode((string)$brief['data'], true);
        $this->view('briefs/show', [
            'title' => $brief['client_label'],
            'brief' => $brief,
            'data' => is_array($data) ? $data : [],
            'schema' => ninubo_brief_schema('en'),
            'link' => Brief::publicLink($brief),
        ]);
    }

    public function destroy(string $id): void
    {
        $brief = Brief::find((int)$id);
        if (!$brief) {
            $this->notFound();
        }
        Brief::delete((int)$id);
        $this->success('Brief "' . $brief['client_label'] . '" deleted.');
        $this->redirect('/briefs');
    }

    /** /admin/brief-ver.php?id=N (link format in older notification emails). */
    public function legacy(): void
    {
        $id = (int)$this->input('id', 0);
        $this->redirect($id > 0 ? '/briefs/' . $id : '/briefs');
    }
}
