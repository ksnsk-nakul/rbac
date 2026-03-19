<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        $tickets = SupportTicket::query()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->latest('id')
            ->get()
            ->map(fn (SupportTicket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'category' => $t->category,
                'last_message_at' => $t->last_message_at?->toIso8601String(),
                'created_at' => $t->created_at?->toIso8601String(),
            ]);

        return Inertia::render('account/settings/SupportTickets', [
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'organization_id' => $orgId,
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'status' => 'open',
            'priority' => $data['priority'] ?? 'normal',
            'category' => $data['category'] ?? null,
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_internal' => false,
            'message' => $data['message'],
        ]);

        ActivityLogger::log('support.ticket_created', $ticket, 'Support ticket created');

        return redirect()->route('account.settings.support.show', $ticket);
    }

    public function show(Request $request, SupportTicket $ticket): Response
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        abort_unless($ticket->organization_id === $orgId, 404);

        $canManage = $user->hasPermission('support.manage');
        abort_unless($canManage || $ticket->user_id === $user->id, 403);

        $ticket->load(['user:id,name,email', 'messages.user:id,name,email']);

        return Inertia::render('account/settings/SupportTicketShow', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'last_message_at' => $ticket->last_message_at?->toIso8601String(),
                'created_at' => $ticket->created_at?->toIso8601String(),
            ],
            'messages' => $ticket->messages
                ->sortBy('id')
                ->values()
                ->map(fn (SupportTicketMessage $m) => [
                    'id' => $m->id,
                    'message' => $m->message,
                    'is_internal' => (bool) $m->is_internal,
                    'user' => $m->user ? [
                        'id' => $m->user->id,
                        'name' => $m->user->name,
                        'email' => $m->user->email,
                    ] : null,
                    'created_at' => $m->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function storeMessage(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        abort_unless($ticket->organization_id === $orgId, 404);

        $canManage = $user->hasPermission('support.manage');
        abort_unless($canManage || $ticket->user_id === $user->id, 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_internal' => false,
            'message' => $data['message'],
        ]);

        $ticket->forceFill([
            'last_message_at' => now(),
        ])->save();

        ActivityLogger::log('support.message_added', $ticket, 'Support ticket message added');

        return redirect()->route('account.settings.support.show', $ticket);
    }

    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        abort_unless($ticket->organization_id === $orgId, 404);

        $canManage = $user->hasPermission('support.manage');
        abort_unless($canManage || $ticket->user_id === $user->id, 403);

        if ($ticket->status !== 'closed') {
            $ticket->forceFill([
                'status' => 'closed',
                'last_message_at' => now(),
            ])->save();

            ActivityLogger::log('support.ticket_closed', $ticket, 'Support ticket closed');
        }

        return redirect()->route('account.settings.support.show', $ticket);
    }
}

