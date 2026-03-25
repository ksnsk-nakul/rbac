<?php

namespace App\Repositories\Contracts;

use App\DTOs\Users\UserListCriteria;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Admin management listing: includes soft-deleted users, excludes the actor.
     */
    public function paginateForAdminManagement(User $actor, UserListCriteria $criteria): LengthAwarePaginator;
}

