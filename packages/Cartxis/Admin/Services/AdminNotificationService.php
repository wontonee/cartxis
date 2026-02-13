<?php

namespace Cartxis\Admin\Services;

use App\Models\User;
use Cartxis\Admin\Models\AdminActivityLog;
use Cartxis\Admin\Models\AdminNotification;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    /**
     * Create notifications for all active admins.
     */
    public function notifyAllAdmins(
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?int $actorUserId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $meta = [],
        string $severity = 'info'
    ): void {
        $adminIds = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($adminIds as $adminId) {
            $rows[] = [
                'recipient_user_id' => $adminId,
                'actor_user_id' => $actorUserId,
                'type' => $type,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'meta' => empty($meta) ? null : json_encode($meta),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('admin_notifications')->insert($rows);
    }

    /**
     * Write activity log entry.
     */
    public function log(
        string $action,
        ?string $description = null,
        ?int $actorUserId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $context = [],
        string $level = 'info'
    ): AdminActivityLog {
        return AdminActivityLog::create([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context' => empty($context) ? null : $context,
            'level' => $level,
        ]);
    }

    public function unreadCountForAdmin(int $adminUserId): int
    {
        return AdminNotification::query()
            ->where('recipient_user_id', $adminUserId)
            ->whereNull('read_at')
            ->count();
    }
}
