<?php
declare(strict_types=1);

use Crm\Controllers\ActivityController;
use Crm\Controllers\AuthController;
use Crm\Controllers\BriefController;
use Crm\Controllers\CatalogController;
use Crm\Controllers\ClientController;
use Crm\Controllers\ContractController;
use Crm\Controllers\DashboardController;
use Crm\Controllers\InvoiceController;
use Crm\Controllers\SubscriptionController;
use Crm\Controllers\ProfileController;
use Crm\Controllers\SettingsController;
use Crm\Controllers\SetupController;
use Crm\Controllers\SystemController;
use Crm\Controllers\UserController;

/** @var \Crm\Core\Router $router */

$public = ['public' => true];
$admin = ['role' => 'admin'];

// Auth
$router->get('/login', [AuthController::class, 'showLogin'], $public);
$router->post('/login', [AuthController::class, 'login'], $public);
$router->post('/logout', [AuthController::class, 'logout']);

// First-run setup (only reachable while there are no users)
$router->get('/setup', [SetupController::class, 'show'], $public);
$router->post('/setup', [SetupController::class, 'run'], $public);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Clients — the starting point of every sale
$router->get('/clients', [ClientController::class, 'index']);
$router->get('/clients/new', [ClientController::class, 'create']);
$router->post('/clients', [ClientController::class, 'store']);
$router->get('/clients/{id}', [ClientController::class, 'show']);
$router->get('/clients/{id}/edit', [ClientController::class, 'edit']);
$router->post('/clients/{id}', [ClientController::class, 'update']);
$router->post('/clients/{id}/delete', [ClientController::class, 'destroy'], $admin);
$router->post('/clients/{id}/activities', [ActivityController::class, 'store']);
$router->post('/activities/{id}/toggle', [ActivityController::class, 'toggle']);
$router->post('/activities/{id}/delete', [ActivityController::class, 'destroy']);

// Invoices (created from a client) and recurring services
$router->get('/invoices', [InvoiceController::class, 'index']);
$router->get('/clients/{id}/invoices/new', [InvoiceController::class, 'create']);
$router->post('/invoices', [InvoiceController::class, 'store']);
$router->get('/invoices/{id}', [InvoiceController::class, 'show']);
$router->get('/invoices/{id}/edit', [InvoiceController::class, 'edit']);
$router->post('/invoices/{id}', [InvoiceController::class, 'update']);
$router->post('/invoices/{id}/issue', [InvoiceController::class, 'issue']);
$router->get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
$router->post('/invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
$router->post('/payments/{id}/delete', [InvoiceController::class, 'deletePayment'], $admin);
$router->post('/invoices/{id}/void', [InvoiceController::class, 'void'], $admin);
$router->post('/invoices/{id}/delete', [InvoiceController::class, 'destroy']);
$router->post('/subscriptions/{id}/invoice', [SubscriptionController::class, 'invoice']);
$router->post('/subscriptions/{id}', [SubscriptionController::class, 'update']);
$router->post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

// Contracts (created from a client, based on an issued invoice; signed at /sign.php?token=...)
$router->get('/contracts', [ContractController::class, 'index']);
$router->get('/clients/{id}/contracts/new', [ContractController::class, 'create']);
$router->post('/contracts', [ContractController::class, 'store']);
$router->get('/contracts/{id}', [ContractController::class, 'show']);
$router->get('/contracts/{id}/edit', [ContractController::class, 'edit']);
$router->post('/contracts/{id}', [ContractController::class, 'update']);
$router->post('/contracts/{id}/finalize', [ContractController::class, 'finalize']);
$router->get('/contracts/{id}/pdf', [ContractController::class, 'pdf']);
$router->post('/contracts/{id}/countersign', [ContractController::class, 'countersign'], $admin);
$router->post('/contracts/{id}/upload', [ContractController::class, 'uploadSigned']);
$router->get('/contracts/{id}/signed-file', [ContractController::class, 'signedFile']);
$router->post('/contracts/{id}/cancel', [ContractController::class, 'cancel'], $admin);
$router->post('/contracts/{id}/delete', [ContractController::class, 'destroy']);

// Packages & services catalog (view: everyone, edit: admin)
$router->get('/catalog', [CatalogController::class, 'index']);
$router->get('/catalog/new', [CatalogController::class, 'create'], $admin);
$router->post('/catalog', [CatalogController::class, 'store'], $admin);
$router->get('/catalog/{id}/edit', [CatalogController::class, 'edit'], $admin);
$router->post('/catalog/{id}', [CatalogController::class, 'update'], $admin);
$router->post('/catalog/{id}/delete', [CatalogController::class, 'destroy'], $admin);

// Website briefs (created from a client + package; the client fills /brief.php?token=...)
$router->get('/briefs', [BriefController::class, 'index']);
$router->post('/briefs', [BriefController::class, 'store']);
$router->get('/briefs/{id}', [BriefController::class, 'show']);
$router->post('/briefs/{id}/reopen', [BriefController::class, 'reopen']);
$router->post('/briefs/{id}/delete', [BriefController::class, 'destroy'], $admin);
// Old notification emails linked to admin/brief-ver.php?id=N
$router->get('/brief-ver.php', [BriefController::class, 'legacy']);

// My profile
$router->get('/profile', [ProfileController::class, 'show']);
$router->post('/profile', [ProfileController::class, 'update']);
$router->get('/profile/password', [ProfileController::class, 'passwordForm']);
$router->post('/profile/password', [ProfileController::class, 'updatePassword']);

// Administration
$router->get('/users', [UserController::class, 'index'], $admin);
$router->get('/users/new', [UserController::class, 'create'], $admin);
$router->post('/users', [UserController::class, 'store'], $admin);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], $admin);
$router->post('/users/{id}', [UserController::class, 'update'], $admin);
$router->post('/users/{id}/reset-password', [UserController::class, 'resetPassword'], $admin);

$router->get('/settings', [SettingsController::class, 'show'], $admin);
$router->post('/settings', [SettingsController::class, 'update'], $admin);
$router->post('/settings/logo/delete', [SettingsController::class, 'deleteLogo'], $admin);
$router->get('/settings/logo', [SettingsController::class, 'logo']);

$router->post('/system/migrate', [SystemController::class, 'migrate'], $admin);
