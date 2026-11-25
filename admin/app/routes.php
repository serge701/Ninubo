<?php
declare(strict_types=1);

use Crm\Controllers\AuthController;
use Crm\Controllers\BriefController;
use Crm\Controllers\DashboardController;
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

// Website briefs (client fills /brief.php?token=...)
$router->get('/briefs', [BriefController::class, 'index']);
$router->post('/briefs', [BriefController::class, 'store']);
$router->get('/briefs/{id}', [BriefController::class, 'show']);
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
