<?php

namespace App\Repositories\Contracts;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Collection;

interface SupportTicketRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * List tickets visible to a customer (their own tickets within org).
     *
     * @return Collection<int, SupportTicket>
     */
    public function listForAccount(User $actor, int $limit = 200): Collection;

    /**
     * List tickets visible to admins (all tickets within org).
     *
     * @return Collection<int, SupportTicket>
     */
    public function listForAdmin(User $actor, ?string $status = null, int $limit = 200): Collection;
}

