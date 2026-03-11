<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function log(
        string $action,
        string $category,
        ?Model $target = null,
        ?string $description = null,
        array $metadata = []
    ): void {
        $request = app(Request::class);
        $actor = $request->user();

        AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_role_id' => $actor?->role_id,
            'action' => $action,
            'category' => $category,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->attributes->get('request_id'),
            'metadata' => $metadata,
        ]);
    }
}
