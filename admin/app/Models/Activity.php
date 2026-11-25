<?php
declare(strict_types=1);

namespace Crm\Models;

use Crm\Core\Database;

/** Client timeline: notes, calls, meetings, follow-up tasks and automatic system entries. */
final class Activity
{
    /** type => [label, Tabler icon, color]. 'system' entries are written by the app only. */
    public const TYPES = [
        'note' => ['Note', 'notes', 'secondary'],
        'call' => ['Call', 'phone', 'blue'],
        'email' => ['Email', 'mail', 'cyan'],
        'whatsapp' => ['WhatsApp', 'brand-whatsapp', 'green'],
        'meeting' => ['Meeting', 'users', 'purple'],
        'task' => ['Follow-up', 'calendar-check', 'orange'],
        'system' => ['Update', 'info-circle', 'secondary'],
    ];

    public static function forClient(int $clientId): array
    {
        return Database::all(
            'SELECT a.*, u.name AS user_name FROM client_activities a LEFT JOIN users u ON u.id = a.user_id
             WHERE a.client_id = ? ORDER BY a.created_at DESC, a.id DESC',
            [$clientId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM client_activities WHERE id = ?', [$id]);
    }

    public static function create(int $clientId, ?int $userId, string $type, string $body, ?string $dueAt = null): int
    {
        $id = Database::insert('client_activities', [
            'client_id' => $clientId,
            'user_id' => $userId,
            'type' => $type,
            'body' => $body,
            'due_at' => $dueAt,
        ]);
        Database::run('UPDATE clients SET updated_at = NOW() WHERE id = ?', [$clientId]);
        return $id;
    }

    /** Automatic timeline entry (client created, status changed, brief sent, ...). */
    public static function log(int $clientId, ?int $userId, string $body): void
    {
        self::create($clientId, $userId, 'system', $body);
    }

    public static function setDone(int $id, bool $done): void
    {
        Database::run('UPDATE client_activities SET done_at = ' . ($done ? 'NOW()' : 'NULL') . ' WHERE id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM client_activities WHERE id = ?', [$id]);
    }

    /** Open follow-ups created by (or for) a user, soonest first. */
    public static function openTasks(int $userId, int $limit = 10): array
    {
        return Database::all(
            "SELECT a.*, c.company_name, c.contact_name FROM client_activities a
             JOIN clients c ON c.id = a.client_id
             WHERE a.type = 'task' AND a.done_at IS NULL AND (a.user_id = ? OR c.assigned_to = ?)
             ORDER BY a.due_at IS NULL, a.due_at, a.id LIMIT " . (int)$limit,
            [$userId, $userId]
        );
    }

    public static function openTaskCount(int $userId): int
    {
        return (int)Database::value(
            "SELECT COUNT(*) FROM client_activities a JOIN clients c ON c.id = a.client_id
             WHERE a.type = 'task' AND a.done_at IS NULL AND (a.user_id = ? OR c.assigned_to = ?)",
            [$userId, $userId]
        );
    }

    public static function overdueCount(int $userId): int
    {
        return (int)Database::value(
            "SELECT COUNT(*) FROM client_activities a JOIN clients c ON c.id = a.client_id
             WHERE a.type = 'task' AND a.done_at IS NULL AND a.due_at < NOW() AND (a.user_id = ? OR c.assigned_to = ?)",
            [$userId, $userId]
        );
    }
}
