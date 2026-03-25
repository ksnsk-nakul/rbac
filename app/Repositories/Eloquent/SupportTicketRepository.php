<?php

namespace App\Repositories\Eloquent;

use App\Models\SupportTicket;
use App\Models\User;
use App\Repositories\Contracts\SupportTicketRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SupportTicketRepository extends BaseRepository implements SupportTicketRepositoryInterface
{
    public function modelClass(): string
    {
        return SupportTicket::class;
    }

    public function listForAccount(User $actor, int $limit = 200): Collection
    {
        $orgId = (int) $actor->current_organization_id;

        return $this->query()
            ->where('organization_id', $orgId)
            ->where('user_id', $actor->id)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function listForAdmin(User $actor, ?string $status = null, int $limit = 200): Collection
    {
        $orgId = (int) $actor->current_organization_id;

        $query = $this->query()
            ->where('organization_id', $orgId)
            ->with(['user:id,name,email'])
            ->latest('last_message_at')
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->limit($limit)->get();
    }
}

