<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Permission;
use App\Services\ActivityLogger;
use App\Services\PlanGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function index(Request $request): Response
    {
        if (!PlanGate::allows($request->user(), 'api_tokens')) {
            abort(403, 'API tokens are not available on your plan.');
        }

        $tokens = $request->user()
            ->apiTokens()
            ->orderByDesc('id')
            ->get()
            ->map(fn (ApiToken $token) => [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toDateTimeString(),
                'created_at' => $token->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('account/settings/ApiTokens', [
            'tokens' => $tokens,
            'newToken' => $request->session()->get('new_token'),
            'availableScopes' => Permission::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!PlanGate::allows($request->user(), 'api_tokens')) {
            abort(403, 'API tokens are not available on your plan.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scopes' => ['array'],
            'scopes.*' => ['string'],
        ]);

        $plainToken = Str::random(40);
        $tokenHash = hash('sha256', $plainToken);

        $token = $request->user()->apiTokens()->create([
            'name' => $validated['name'],
            'token_hash' => $tokenHash,
            'scopes' => $validated['scopes'] ?? [],
        ]);

        ActivityLogger::log('api_token.created', $token, 'API token created', $request->user()->id);

        return back()->with('new_token', $plainToken);
    }

    public function destroy(Request $request, ApiToken $token): RedirectResponse
    {
        if (!PlanGate::allows($request->user(), 'api_tokens')) {
            abort(403, 'API tokens are not available on your plan.');
        }

        if ($token->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        $token->delete();

        ActivityLogger::log('api_token.revoked', $token, 'API token revoked', $request->user()->id);

        return back();
    }
}
