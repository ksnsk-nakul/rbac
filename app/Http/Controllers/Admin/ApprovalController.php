<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Services\ApprovalService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function index(): Response
    {
        $requests = ApprovalRequest::orderByDesc('id')
            ->get()
            ->map(fn (ApprovalRequest $request) => [
                'id' => $request->id,
                'type' => $request->type,
                'status' => $request->status,
                'requested_by' => $request->requested_by,
                'approved_by' => $request->approved_by,
                'created_at' => $request->created_at?->toDateTimeString(),
                'payload' => $request->payload,
            ]);

        return Inertia::render('admin/Approvals', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, ApprovalRequest $approval): RedirectResponse
    {
        $user = $request->user();
        ApprovalService::approve($approval, $user);

        $payload = $approval->payload ?? [];

        if ($approval->type === 'role.create') {
            $role = Role::create([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'route' => $payload['route'],
                'is_subadmin' => false,
                'is_default' => $payload['is_default'] ?? false,
            ]);
            $role->permissions()->sync($payload['permissions'] ?? []);
            ActivityLogger::log('role.created', $role, 'Role created (approved)');
        }

        if ($approval->type === 'role.update') {
            $role = Role::findOrFail($payload['role_id']);
            $role->update([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'route' => $payload['route'],
                'is_default' => $payload['is_default'] ?? false,
            ]);
            $role->permissions()->sync($payload['permissions'] ?? []);
            ActivityLogger::log('role.updated', $role, 'Role updated (approved)');
        }

        if ($approval->type === 'role.delete') {
            $role = Role::find($payload['role_id']);
            if ($role) {
                ActivityLogger::log('role.deleted', $role, 'Role deleted (approved)');
                $role->delete();
            }
        }

        ApprovalService::markApplied($approval);

        return back();
    }
}
