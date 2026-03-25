<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Users\UserListCriteria;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function modelClass(): string
    {
        return User::class;
    }

    public function paginateForAdminManagement(User $actor, UserListCriteria $criteria): LengthAwarePaginator
    {
        $q = trim($criteria->q);

        $query = $this->query()
            ->with('role')
            ->withTrashed()
            ->where('id', '!=', $actor->id);

        if ($q !== '') {
            $query->where(function (Builder $qb) use ($q) {
                $qb->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
            });
        }

        if ($criteria->role) {
            $query->whereHas('role', fn (Builder $qb) => $qb->where('slug', $criteria->role));
        }

        if ($criteria->status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($criteria->status === 'banned') {
            $query->whereNotNull('deleted_at');
        }

        $query->orderBy($criteria->normalizedSort(), $criteria->normalizedDir());

        $perPage = max(1, min(100, (int) $criteria->perPage));

        return $query
            ->paginate($perPage);
    }
}

