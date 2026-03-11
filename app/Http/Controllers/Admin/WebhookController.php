<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebhookController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Webhooks', [
            'endpoints' => WebhookEndpoint::orderByDesc('id')->get(),
            'events' => \App\Services\SecurityWebhookService::EVENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url'],
            'secret' => ['nullable', 'string', 'max:255'],
            'events' => ['required', 'array'],
            'events.*' => ['string'],
        ]);

        WebhookEndpoint::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $validated['secret'] ?? null,
            'events' => $validated['events'],
            'active' => true,
        ]);

        return back();
    }

    public function destroy(WebhookEndpoint $endpoint): RedirectResponse
    {
        $endpoint->delete();

        return back();
    }
}
