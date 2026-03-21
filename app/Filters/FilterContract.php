<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterContract
{
    public function apply(Builder $query): Builder;
}

