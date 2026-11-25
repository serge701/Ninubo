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

/**
 * Bootstrap/Tabler form field wrapped in a grid column, with label, validation error and hint.
 * Options: type (text|email|tel|url|number|date|datetime-local|textarea|select), options (for select:
 * value => label), required, hint, col (default col-md-6), placeholder, rows, step, empty (select blank label),
 * attrs (extra raw attributes).
 */
function form_field(string $name, string $label, mixed $value, array $o = []): string
{
    $type = $o['type'] ?? 'text';
    $id = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $cls = invalid($name);
    $req = !empty($o['required']) ? ' required' : '';
    $extra = ($o['attrs'] ?? '') . (isset($o['placeholder']) ? ' placeholder="' . e($o['placeholder']) . '"' : '');

    $html = '<div class="' . e($o['col'] ?? 'col-md-6') . '">'
        . '<label class="form-label' . ($req ? ' required' : '') . '" for="' . $id . '">' . e($label) . '</label>';

    if ($type === 'textarea') {
        $html .= '<textarea class="form-control' . $cls . '" id="' . $id . '" name="' . e($name) . '" rows="' . (int)($o['rows'] ?? 3) . '"' . $req . $extra . '>' . e($value) . '</textarea>';
    } elseif ($type === 'select') {
        $html .= '<select class="form-select' . $cls . '" id="' . $id . '" name="' . e($name) . '"' . $req . $extra . '>';
        if (array_key_exists('empty', $o)) {
            $html .= '<option value="">' . e($o['empty']) . '</option>';
        }
        foreach ($o['options'] ?? [] as $optValue => $optLabel) {
            $sel = (string)$optValue === (string)$value ? ' selected' : '';
            $html .= '<option value="' . e($optValue) . '"' . $sel . '>' . e($optLabel) . '</option>';
        }
        $html .= '</select>';
    } else {
        $step = isset($o['step']) ? ' step="' . e($o['step']) . '"' : '';
        $input = '<input type="' . e($type) . '" class="form-control' . $cls . '" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . $step . $req . $extra . '>';
        if (isset($o['prefix']) || isset($o['suffix'])) {
            $input = '<div class="input-group">'
                . (isset($o['prefix']) ? '<span class="input-group-text">' . e($o['prefix']) . '</span>' : '')
                . $input
                . (isset($o['suffix']) ? '<span class="input-group-text">' . e($o['suffix']) . '</span>' : '')
                . '</div>';
        }
        $html .= $input;
    }

    if ($err = field_error($name)) {
        $html .= '<div class="invalid-feedback d-block">' . e($err) . '</div>';
    }
    if (!empty($o['hint'])) {
        $html .= '<div class="form-hint">' . e($o['hint']) . '</div>';
    }
    return $html . '</div>';
}

/** Tabler switch (checkbox) in a grid column. */
function form_switch(string $name, string $label, bool $checked, array $o = []): string
{
    return '<div class="' . e($o['col'] ?? 'col-md-6') . '"><label class="form-check form-switch mt-md-4">'
        . '<input class="form-check-input" type="checkbox" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '>'
        . '<span class="form-check-label">' . e($label) . '</span></label>'
        . (!empty($o['hint']) ? '<div class="form-hint">' . e($o['hint']) . '</div>' : '') . '</div>';
}

/** "3 hours ago" style relative time. */
function time_ago(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $diff = time() - strtotime($value);
    if ($diff < 60) {
        return 'just now';
    }
    foreach ([86400 * 30 => 'month', 86400 * 7 => 'week', 86400 => 'day', 3600 => 'hour', 60 => 'minute'] as $secs => $unit) {
        if ($diff >= $secs) {
            $n = (int)floor($diff / $secs);
            return $n . ' ' . $unit . ($n === 1 ? '' : 's') . ' ago';
        }
    }
    return fmt_date($value);
}

/** Current query string with some parameters replaced (for filters / pagination links). */
function query_with(array $changes): string
{
    $params = array_filter(array_merge($_GET, $changes), fn($v) => $v !== null && $v !== '');
    return $params ? '?' . http_build_query($params) : '';
}

/** Tabler badge for a status value, using a color map. */
function status_badge(string $status, array $colors, array $labels = []): string
{
    $color = $colors[$status] ?? 'secondary';
    $label = $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    return '<span class="badge bg-' . e($color) . '-lt">' . e($label) . '</span>';
}
