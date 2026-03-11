<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalService
{
    public static function requiresApproval(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public static function create(string $type, array $payload, User $user): ApprovalRequest
    {
        return ApprovalRequest::create([
            'type' => $type,
            'payload' => $payload,
            'status' => 'pending',
            'requested_by' => $user->id,
        ]);
    }

    public static function approve(ApprovalRequest $request, User $approver): ApprovalRequest
    {
        $request->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $request;
    }

    public static function markApplied(ApprovalRequest $request): void
    {
        $request->update([
            'status' => 'applied',
            'applied_at' => now(),
        ]);
    }
}
