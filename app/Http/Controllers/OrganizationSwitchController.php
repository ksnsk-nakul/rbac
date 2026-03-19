<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'organization_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        abort_unless($user, 401);

        $orgId = (int) $request->input('organization_id');

        $hasMembership = $user->organizations()
            ->where('organizations.id', $orgId)
            ->wherePivot('status', 'active')
            ->exists();

        abort_unless($hasMembership, 403);

        $user->forceFill(['current_organization_id' => $orgId])->save();

        $org = Organization::find($orgId);
        ActivityLogger::log(
            $request,
            'organization.switched',
            $org,
            $org ? "Switched to organization: {$org->name}" : 'Switched organization'
        );

        return back();
    }
}
