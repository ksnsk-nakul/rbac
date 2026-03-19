<?php

namespace App\Http\Controllers\Admin;

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

        $status = $request->string('status')->toString();
        $status = $status !== '' ? $status : null;

        $ticketsQuery = SupportTicket::query()
            ->where('organization_id', $orgId)
            ->with(['user:id,name,email'])
            ->latest('last_message_at')
            ->latest('id');

        if ($status) {
            $ticketsQuery->where('status', $status);
        }

        $tickets = $ticketsQuery
            ->limit(200)
            ->get()
            ->map(fn (SupportTicket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'category' => $t->category,
                'last_message_at' => $t->last_message_at?->toIso8601String(),
                'created_at' => $t->created_at?->toIso8601String(),
                'user' => $t->user ? [
                    'id' => $t->user->id,
                    'name' => $t->user->name,
                    'email' => $t->user->email,
                ] : null,
            ]);

        return Inertia::render('admin/SupportTickets', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function show(Request $request, SupportTicket $ticket): Response
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        abort_unless($ticket->organization_id === $orgId, 404);

        $ticket->load(['user:id,name,email', 'messages.user:id,name,email']);

        return Inertia::render('admin/SupportTicketShow', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'last_message_at' => $ticket->last_message_at?->toIso8601String(),
                'created_at' => $ticket->created_at?->toIso8601String(),
                'user' => $ticket->user ? [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                    'email' => $ticket->user->email,
                ] : null,
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

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_internal' => (bool) ($data['is_internal'] ?? false),
            'message' => $data['message'],
        ]);

        $ticket->forceFill([
            'last_message_at' => now(),
        ])->save();

        ActivityLogger::log('support.admin_message_added', $ticket, 'Support ticket admin message added');

        return redirect()->route('admin.support.show', $ticket);
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $user = $request->user();
        $orgId = $user->current_organization_id;

        abort_unless($ticket->organization_id === $orgId, 404);

        $data = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,waiting,closed'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $dirty = [];
        foreach (['status', 'priority', 'category'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $dirty[$k] = $data[$k];
            }
        }

        if ($dirty !== []) {
            $ticket->forceFill($dirty + ['last_message_at' => now()])->save();
            ActivityLogger::log('support.ticket_updated', $ticket, 'Support ticket updated');
        }

        return redirect()->route('admin.support.show', $ticket);
    }
}

