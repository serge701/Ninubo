<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Brute-force protection for the sign-in form:
 *  - 5 failures for the same username from the same IP within 15 min lock that pair;
 *  - 30 failures from one IP (any username) within 15 min lock the IP.
 * A successful sign-in clears the pair. Rows older than a day are pruned.
 */
final class LoginThrottle
{
    private const WINDOW_MINUTES = 15;
    private const MAX_PER_USER = 5;
    private const MAX_PER_IP = 30;

    /** Minutes until another attempt is allowed; 0 = allowed now. */
    public static function lockedMinutes(string $username, string $ip): int
    {
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_MINUTES * 60);

        $pair = Database::one(
            'SELECT COUNT(*) AS n, MIN(created_at) AS first FROM login_attempts WHERE username = ? AND ip = ? AND created_at >= ?',
            [self::key($username), $ip, $since]
        );
        $byIp = Database::one(
            'SELECT COUNT(*) AS n, MIN(created_at) AS first FROM login_attempts WHERE ip = ? AND created_at >= ?',
            [$ip, $since]
        );

        $first = null;
        if ((int)$pair['n'] >= self::MAX_PER_USER) {
            $first = $pair['first'];
        } elseif ((int)$byIp['n'] >= self::MAX_PER_IP) {
            $first = $byIp['first'];
        }
        if ($first === null) {
            return 0;
        }
        return max(1, (int)ceil((strtotime($first) + self::WINDOW_MINUTES * 60 - time()) / 60));
    }

    public static function failed(string $username, string $ip): void
    {
        Database::run(
            'INSERT INTO login_attempts (username, ip, created_at) VALUES (?, ?, ?)',
            [self::key($username), $ip, date('Y-m-d H:i:s')]
        );
        Database::run('DELETE FROM login_attempts WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
    }

    public static function succeeded(string $username, string $ip): void
    {
        Database::run('DELETE FROM login_attempts WHERE username = ? AND ip = ?', [self::key($username), $ip]);
    }

    private static function key(string $username): string
    {
        return mb_substr(mb_strtolower(trim($username)), 0, 100);
    }
}
