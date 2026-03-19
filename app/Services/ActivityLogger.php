<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Services\SecurityWebhookService;

class ActivityLogger
{
    public static function log(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        ?int $userId = null
    ): void {
        $request = app(Request::class);
        $user = $request->user();
        $now = now();
        $previousHash = ActivityLog::orderByDesc('id')->value('event_hash');

        $payload = [
            'user_id' => $userId ?? $user?->id,
            'organization_id' => $user?->current_organization_id,
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => $now->toIso8601String(),
        ];

        $eventHash = hash('sha256', json_encode($payload) . '|' . ($previousHash ?? ''));

        $log = ActivityLog::create(Arr::except($payload, ['created_at']) + [
            'event_hash' => $eventHash,
            'previous_hash' => $previousHash,
            'created_at' => $now,
        ]);

        $eventMap = [
            'login' => 'user_login',
            'logout' => 'user_logout',
            'role.created' => 'role_changed',
            'role.updated' => 'role_changed',
            'role.deleted' => 'role_changed',
            'permissions.assigned' => 'permission_changed',
            'permissions.changed' => 'permission_changed',
            'api_token.created' => 'token_created',
            'api_token.revoked' => 'token_revoked',
        ];

        $event = $eventMap[$action] ?? null;

        if (!$event) {
            return;
        }

        SecurityWebhookService::dispatch($event, [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'organization_id' => $log->organization_id,
            'action' => $log->action,
            'model_type' => $log->model_type,
            'model_id' => $log->model_id,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'created_at' => $log->created_at?->toIso8601String(),
        ]);
    }
}
