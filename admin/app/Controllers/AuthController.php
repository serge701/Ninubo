<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Auth;
use Crm\Core\Controller;
use Crm\Core\Session;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->view('auth/login', ['title' => 'Sign in'], 'layouts/auth');
    }

    public function login(): void
    {
        $username = (string)$this->input('username');
        $password = (string)($_POST['password'] ?? '');

        $error = ($username === '' || $password === '')
            ? 'Enter your username and password.'
            : Auth::attempt($username, $password);

        if ($error !== null) {
            Session::flash('danger', $error);
            Session::flash('old', ['username' => $username]);
            $this->redirect('/login');
        }

        $intended = (string)Session::get('intended', '/');
        Session::remove('intended');
        $this->redirect(str_starts_with($intended, '/') && !str_starts_with($intended, '//') ? $intended : '/');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('success', 'You have been signed out.');
        $this->redirect('/login');
    }
}
