<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Models\User;

final class ProfileController extends Controller
{
    public function show(): void
    {
        $this->view('profile/index', ['title' => 'My profile', 'profile' => $this->user()]);
    }

    public function update(): void
    {
        $user = $this->user();
        $data = [
            'name' => (string)$this->input('name'),
            'email' => (string)$this->input('email'),
            'phone' => (string)$this->input('phone'),
        ];
        $errors = User::validate($data, (int)$user['id'], false);
        if ($errors) {
            $this->back('/profile', $errors, $data);
        }
        User::update((int)$user['id'], $data);
        $this->success('Profile updated.');
        $this->redirect('/profile');
    }

    public function passwordForm(): void
    {
        $this->view('profile/password', [
            'title' => 'Change password',
            'forced' => (bool)$this->user()['must_change_password'],
        ]);
    }

    public function updatePassword(): void
    {
        $user = $this->user();
        $current = (string)($_POST['current_password'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        $errors = [];
        if (!password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'Your current password is not correct.';
        }
        if ($pwError = User::passwordError($password, $confirm)) {
            $errors['password'] = $pwError;
        } elseif (password_verify($password, $user['password_hash'])) {
            $errors['password'] = 'Choose a password different from the current one.';
        }
        if ($errors) {
            $this->back('/profile/password', $errors);
        }

        User::setPassword((int)$user['id'], $password, false);
        Session::regenerate();
        $this->success('Password changed.');
        $this->redirect('/');
    }
}
