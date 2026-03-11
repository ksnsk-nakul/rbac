<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpAllowlistEntry;
use App\Models\Role;
use App\Services\PlanGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IpAllowlistController extends Controller
{
    public function index(Request $request): Response
    {
        if (!PlanGate::allows($request->user(), 'ip_allowlist')) {
            abort(403, 'IP allowlist is not available on your plan.');
        }

        $roles = Role::orderBy('name')
            ->with('ipAllowlistEntries')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'require_ip_allowlist' => $role->require_ip_allowlist,
                'entries' => $role->ipAllowlistEntries->map(fn (IpAllowlistEntry $entry) => [
                    'id' => $entry->id,
                    'ip_address' => $entry->ip_address,
                    'label' => $entry->label,
                ]),
            ]);

        return Inertia::render('admin/IpAllowlist', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!PlanGate::allows($request->user(), 'ip_allowlist')) {
            abort(403, 'IP allowlist is not available on your plan.');
        }

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'ip_address' => ['required', 'string', 'max:64'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        IpAllowlistEntry::create($validated);

        return back();
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        if (!PlanGate::allows($request->user(), 'ip_allowlist')) {
            abort(403, 'IP allowlist is not available on your plan.');
        }

        $validated = $request->validate([
            'require_ip_allowlist' => ['required', 'boolean'],
        ]);

        $role->update(['require_ip_allowlist' => $validated['require_ip_allowlist']]);

        return back();
    }

    public function destroy(Request $request, IpAllowlistEntry $entry): RedirectResponse
    {
        if (!PlanGate::allows($request->user(), 'ip_allowlist')) {
            abort(403, 'IP allowlist is not available on your plan.');
        }

        $entry->delete();

        return back();
    }
}
