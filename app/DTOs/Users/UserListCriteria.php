<?php

namespace App\DTOs\Users;

/**
 * Typed criteria for user listing screens.
 *
 * Controllers are responsible for parsing Request into this DTO.
 * Repositories only use this DTO to build queries (no Request coupling).
 */
class UserListCriteria
{
    public function __construct(
        public string $q = '',
        public ?string $role = null,
        public string $status = 'all', // all|active|banned
        public string $sort = 'name', // name|email|role|created_at
        public string $dir = 'asc', // asc|desc
        public int $perPage = 15,
        public string $view = 'list',
    ) {
    }

    public function normalizedDir(): string
    {
        return strtolower($this->dir) === 'desc' ? 'desc' : 'asc';
    }

    public function normalizedSort(): string
    {
        return match ($this->sort) {
            'email' => 'email',
            'role' => 'role_id',
            'created_at' => 'created_at',
            default => 'name',
        };
    }
}

