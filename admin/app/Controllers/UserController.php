<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->view('users/index', ['title' => 'Users', 'users' => User::all()]);
    }

    public function create(): void
    {
        $this->view('users/form', ['title' => 'New user', 'editUser' => null]);
    }

    public function store(): void
    {
        $data = $this->formData(true);
        $errors = User::validate($data);
        if ($errors) {
            $this->back('/users/new', $errors, $data);
        }
        $temp = User::temporaryPassword();
        $id = User::create($data, $temp, true);
        Session::flash('temp_password', ['username' => $data['username'], 'password' => $temp]);
        $this->success('User created. Share the temporary password below; they will be asked to change it on first sign-in.');
        $this->redirect('/users/' . $id . '/edit');
    }

    public function edit(string $id): void
    {
        $user = User::find((int)$id) ?? $this->notFound();
        $this->view('users/form', ['title' => 'Edit user', 'editUser' => $user]);
    }

    public function update(string $id): void
    {
        $user = User::find((int)$id) ?? $this->notFound();
        $data = $this->formData(false);
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;

        $errors = User::validate($data, (int)$user['id'], false);
        $isSelf = (int)$user['id'] === (int)$this->user()['id'];
        $wasActiveAdmin = $user['role'] === 'admin' && (int)$user['is_active'] === 1;
        $staysActiveAdmin = $data['role'] === 'admin' && $data['is_active'] === 1;
        if ($isSelf && !$staysActiveAdmin) {
            $errors['role'] = 'You cannot remove your own administrator access or disable yourself.';
        } elseif ($wasActiveAdmin && !$staysActiveAdmin && User::activeAdminCount() <= 1) {
            $errors['role'] = 'There must be at least one active administrator.';
        }
        if ($errors) {
            $this->back('/users/' . $id . '/edit', $errors, $data);
        }

        User::update((int)$user['id'], $data);
        $this->success('User updated.');
        $this->redirect('/users');
    }

    public function resetPassword(string $id): void
    {
        $user = User::find((int)$id) ?? $this->notFound();
        $temp = User::temporaryPassword();
        User::setPassword((int)$user['id'], $temp, true);
        Session::flash('temp_password', ['username' => $user['username'], 'password' => $temp]);
        $this->success('Password reset. Share the temporary password below.');
        $this->redirect('/users/' . $id . '/edit');
    }

    private function formData(bool $withUsername): array
    {
        $data = [
            'name' => (string)$this->input('name'),
            'email' => (string)$this->input('email'),
            'phone' => (string)$this->input('phone'),
            'role' => array_key_exists((string)$this->input('role'), User::ROLES) ? (string)$this->input('role') : 'sales',
        ];
        if ($withUsername) {
            $data = ['username' => mb_strtolower((string)$this->input('username'))] + $data;
        }
        return $data;
    }
}
