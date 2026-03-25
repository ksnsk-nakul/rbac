<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @return class-string<Model>
     */
    abstract public function modelClass(): string;

    public function query(): Builder
    {
        /** @var class-string<Model> $model */
        $model = $this->modelClass();

        return $model::query();
    }
}

