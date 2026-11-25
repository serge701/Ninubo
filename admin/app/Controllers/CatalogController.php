<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\Package;

/** Packages & services. Everyone can view (prices for quoting); only admins edit. */
final class CatalogController extends Controller
{
    public function index(): void
    {
        $this->view('catalog/index', ['title' => 'Packages & services', 'packages' => Package::all()]);
    }

    public function create(): void
    {
        $this->view('catalog/form', ['title' => 'New package or service', 'package' => null]);
    }

    public function store(): void
    {
        $data = Package::fromInput($_POST + ['is_active' => 1]);
        if ($errors = Package::validate($data)) {
            $this->back('/catalog/new', $errors, $_POST);
        }
        Package::create($data);
        $this->success('"' . $data['name'] . '" added to the catalog.');
        $this->redirect('/catalog');
    }

    public function edit(string $id): void
    {
        $package = Package::find((int)$id) ?? $this->notFound();
        $this->view('catalog/form', ['title' => 'Edit ' . $package['name'], 'package' => $package]);
    }

    public function update(string $id): void
    {
        $package = Package::find((int)$id) ?? $this->notFound();
        $data = Package::fromInput($_POST);
        if ($errors = Package::validate($data)) {
            $this->back('/catalog/' . $id . '/edit', $errors, $_POST);
        }
        Package::update((int)$package['id'], $data);
        $this->success('"' . $data['name'] . '" updated.');
        $this->redirect('/catalog');
    }

    public function destroy(string $id): void
    {
        $package = Package::find((int)$id) ?? $this->notFound();
        if (Package::isUsed((int)$package['id'])) {
            Session::flash('warning', '"' . $package['name'] . '" is already used, so it was not deleted. Mark it inactive instead.');
            $this->redirect('/catalog/' . $id . '/edit');
        }
        Package::delete((int)$package['id']);
        $this->success('"' . $package['name'] . '" deleted.');
        $this->redirect('/catalog');
    }
}
