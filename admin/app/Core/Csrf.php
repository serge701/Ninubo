<?php
declare(strict_types=1);

namespace Crm\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    /** New token, e.g. after sign-in. */
    public static function rotate(): void
    {
        Session::set('_csrf', bin2hex(random_bytes(32)));
    }

    public static function verify(mixed $token): bool
    {
        $expected = Session::get('_csrf');
        return is_string($token) && $token !== '' && is_string($expected) && hash_equals($expected, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
