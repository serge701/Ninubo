<?php
declare(strict_types=1);

namespace Crm\Core;

use Crm\Models\User;

/** Username + password sign-in against the `users` table. */
final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    /** The signed-in user (fresh from the DB each request), or null. Inactive users are signed out. */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;

        $id = Session::get('user_id');
        if (!$id) {
            return null;
        }
        $user = User::find((int)$id);
        if (!$user || !(int)$user['is_active']) {
            Session::remove('user_id');
            return null;
        }
        return self::$user = $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * @return string|null error message, or null on success
     */
    public static function attempt(string $username, string $password): ?string
    {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $locked = LoginThrottle::lockedMinutes($username, $ip);
        if ($locked > 0) {
            return "Too many failed attempts. Try again in {$locked} min.";
        }

        $user = User::findByUsername($username);
        // Always run password_verify so response time doesn't reveal whether the username exists.
        $hash = $user['password_hash'] ?? password_hash(random_bytes(8), PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash);

        if (!$user || !$valid) {
            LoginThrottle::failed($username, $ip);
            usleep(400000);
            return 'Invalid username or password.';
        }
        if (!(int)$user['is_active']) {
            return 'This account is disabled. Contact an administrator.';
        }

        LoginThrottle::succeeded($username, $ip);
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::setPassword((int)$user['id'], $password, (bool)$user['must_change_password']);
        }
        self::login($user);
        return null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Csrf::rotate();
        Session::set('user_id', (int)$user['id']);
        User::touchLogin((int)$user['id']);
        self::$user = $user;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
    }
}
