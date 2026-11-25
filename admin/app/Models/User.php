<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

final class User
{
    public const ROLES = ['admin' => 'Administrator', 'sales' => 'Sales'];

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByUsername(string $username): ?array
    {
        return Database::one('SELECT * FROM users WHERE username = ?', [mb_strtolower(trim($username))]);
    }

    public static function all(): array
    {
        return Database::all('SELECT * FROM users ORDER BY is_active DESC, name');
    }

    public static function activeAdminCount(): int
    {
        return (int)Database::value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1");
    }

    public static function create(array $data, string $password, bool $mustChange): int
    {
        return Database::insert('users', [
            'username' => mb_strtolower(trim($data['username'])),
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'role' => $data['role'],
            'is_active' => 1,
            'must_change_password' => $mustChange ? 1 : 0,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        Database::update('users', $id, $data);
    }

    public static function setPassword(int $id, string $password, bool $mustChange): void
    {
        Database::update('users', $id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => $mustChange ? 1 : 0,
        ]);
    }

    public static function touchLogin(int $id): void
    {
        Database::run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    /**
     * Validates profile fields. @return array<string,string> field => error
     */
    public static function validate(array $data, ?int $ignoreId = null, bool $checkUsername = true): array
    {
        $errors = [];
        if ($checkUsername) {
            $username = mb_strtolower(trim((string)($data['username'] ?? '')));
            if (!preg_match('/^[a-z0-9._-]{3,60}$/', $username)) {
                $errors['username'] = '3–60 characters: letters, numbers, dot, dash or underscore.';
            } else {
                $existing = self::findByUsername($username);
                if ($existing && (int)$existing['id'] !== $ignoreId) {
                    $errors['username'] = 'That username is already taken.';
                }
            }
        }
        if (mb_strlen(trim((string)($data['name'] ?? ''))) < 2) {
            $errors['name'] = 'Enter the full name.';
        }
        $email = trim((string)($data['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        return $errors;
    }

    /** @return string|null error, or null if the password is acceptable */
    public static function passwordError(string $password, string $confirm): ?string
    {
        if (mb_strlen($password) < 10) {
            return 'Use at least 10 characters.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Use letters and at least one number.';
        }
        if ($password !== $confirm) {
            return 'The passwords do not match.';
        }
        return null;
    }

    /** Random temporary password (shown once to the admin). */
    public static function temporaryPassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < 12; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $out . random_int(10, 99);
    }
}
