<?php

namespace Cartxis\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\Admin\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('admin');
        $limit = max(1, min((int) $request->input('limit', 12), 50));

        $notifications = AdminNotification::query()
            ->where('recipient_user_id', $user->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (AdminNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'severity' => $notification->severity,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'action_url' => $notification->action_url,
                    'entity_type' => $notification->entity_type,
                    'entity_id' => $notification->entity_id,
                    'meta' => $notification->meta,
                    'read_at' => optional($notification->read_at)?->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String(),
                    'created_at_human' => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = AdminNotification::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, AdminNotification $notification): JsonResponse
    {
        $user = $request->user('admin');

        if ((int) $notification->recipient_user_id !== (int) $user->id) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user('admin');

        AdminNotification::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
