<?php
declare(strict_types=1);

use Crm\Core\Auth;
use Crm\Core\Csrf;
use Crm\Core\Session;

/** HTML-escape shorthand for views. */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** CRM URL: url('/clients/5') -> "/ninubo/admin/clients/5". */
function url(string $path = '/'): string
{
    return CRM_BASE . '/' . ltrim($path, '/');
}

/** URL of a file under admin/assets/, cache-busted by its modification time. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $mtime = @filemtime(CRM_ROOT . '/assets/' . $path);
    return CRM_BASE . '/assets/' . $path . ($mtime ? '?v=' . $mtime : '');
}

function csrf_field(): string
{
    return Csrf::field();
}

function auth_user(): ?array
{
    return Auth::user();
}

function is_admin(): bool
{
    return (Auth::user()['role'] ?? null) === 'admin';
}

/** Previous form input after a failed validation (flashed by Controller::back()). */
function old(string $key, mixed $default = ''): mixed
{
    static $old = null;
    if ($old === null) {
        $old = Session::peekFlash('old') ?? [];
    }
    return $old[$key] ?? $default;
}

/** Validation error for a field, or null. */
function field_error(string $key): ?string
{
    static $errors = null;
    if ($errors === null) {
        $errors = Session::peekFlash('errors') ?? [];
    }
    return $errors[$key] ?? null;
}

/** Adds Tabler's is-invalid class when the field has an error. */
function invalid(string $key): string
{
    return field_error($key) !== null ? ' is-invalid' : '';
}

function fmt_date(?string $value): string
{
    return $value ? date('M j, Y', strtotime($value)) : '—';
}

function fmt_datetime(?string $value): string
{
    return $value ? date('M j, Y g:i A', strtotime($value)) : '—';
}

function money(float|int|string|null $amount, string $currency = 'USD'): string
{
    $symbol = $currency === 'USD' ? '$' : '';
    return $symbol . number_format((float)$amount, 2) . ($symbol === '' ? ' ' . $currency : '');
}

/** "Sergio Ureña" -> "SU". */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [$name];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr((string)end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

/** Tabler badge for a status value, using a color map. */
function status_badge(string $status, array $colors, array $labels = []): string
{
    $color = $colors[$status] ?? 'secondary';
    $label = $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    return '<span class="badge bg-' . e($color) . '-lt">' . e($label) . '</span>';
}
