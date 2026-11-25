<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Auth;
use Crm\Core\Controller;
use Crm\Core\Migrator;
use Crm\Core\Session;
use Crm\Models\User;

/**
 * One-time setup, reachable only while no user exists (enforced in App::dispatch).
 * Proves ownership with the previous single admin-panel password (NINUBO_ADMIN_PASSWORD_HASH
 * in includes/config.php), creates the tables and the first administrator.
 */
final class SetupController extends Controller
{
    public function show(): void
    {
        $this->view('setup/index', ['title' => 'Set up the CRM', 'pending' => Migrator::pending()], 'layouts/auth');
    }

    public function run(): void
    {
        $data = [
            'username' => (string)$this->input('username'),
            'name' => (string)$this->input('name'),
            'email' => (string)$this->input('email'),
        ];
        $setupKey = (string)($_POST['setup_key'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        if (!password_verify($setupKey, NINUBO_ADMIN_PASSWORD_HASH)) {
            sleep(2);
            $this->back('/setup', ['setup_key' => 'That is not the current admin-panel password.'], $data);
        }

        Migrator::migrate();

        $errors = User::validate($data);
        if ($pwError = User::passwordError($password, $confirm)) {
            $errors['password'] = $pwError;
        }
        if ($errors) {
            $this->back('/setup', $errors, $data);
        }

        $id = User::create($data + ['role' => 'admin'], $password, false);
        Auth::login(User::find($id));
        Session::flash('success', 'Welcome! Your administrator account is ready. Start by filling in your business settings.');
        $this->redirect('/settings');
    }
}
