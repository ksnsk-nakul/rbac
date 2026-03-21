<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilters extends QueryFilter
{
    protected function filters(): array
    {
        return [
            ...parent::filters(),
            'role' => $this->request->query('role'),
            'status' => $this->request->query('status'), // active|banned|all
        ];
    }

    public function q(Builder $query, string $value): Builder
    {
        $value = trim($value);
        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($value) {
            $q->where('name', 'like', '%'.$value.'%')
                ->orWhere('email', 'like', '%'.$value.'%');
        });
    }

    public function role(Builder $query, string $value): Builder
    {
        return $query->whereHas('role', fn (Builder $q) => $q->where('slug', $value));
    }

    public function status(Builder $query, string $value): Builder
    {
        return match ($value) {
            'active' => $query->whereNull('deleted_at'),
            'banned' => $query->whereNotNull('deleted_at'),
            default => $query,
        };
    }

    public function sort(Builder $query, string $value): Builder
    {
        $dir = strtolower((string) $this->request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $column = match ($value) {
            'email' => 'email',
            'role' => 'role_id',
            'created_at' => 'created_at',
            default => 'name',
        };

        return $query->orderBy($column, $dir);
    }
}

