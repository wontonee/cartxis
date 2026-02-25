<?php

namespace Cartxis\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\Admin\Models\AdminActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ActivityLogController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Admin/System/ActivityLogs/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->input('limit', 25), 100));

        $logs = AdminActivityLog::query()
            ->with('actor:id,name,email')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (AdminActivityLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'level' => $log->level,
                    'description' => $log->description,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'context' => $log->context,
                    'actor' => $log->actor ? [
                        'id' => $log->actor->id,
                        'name' => $log->actor->name,
                        'email' => $log->actor->email,
                    ] : null,
                    'created_at' => $log->created_at->toIso8601String(),
                    'created_at_human' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
