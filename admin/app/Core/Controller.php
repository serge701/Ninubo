<?php
declare(strict_types=1);

namespace Crm\Core;

abstract class Controller
{
    protected function view(string $name, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($name, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    /** Redirect back to a form with its input and validation errors. */
    protected function back(string $path, array $errors, array $old = []): never
    {
        unset($old['_csrf'], $old['password'], $old['password_confirm'], $old['current_password']);
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        Session::flash('danger', 'Please fix the highlighted fields.');
        $this->redirect($path);
    }

    protected function success(string $message): void
    {
        Session::flash('success', $message);
    }

    protected function input(string $key, mixed $default = ''): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function user(): array
    {
        return Auth::user() ?? [];
    }

    protected function notFound(): never
    {
        App::abort(404, 'Not found', 'That record does not exist or was deleted.');
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
