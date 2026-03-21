<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter implements FilterContract
{
    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters() as $method => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (! method_exists($this, $method)) {
                continue;
            }
            $query = $this->{$method}($query, $value);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(): array
    {
        return [
            'q' => $this->request->query('q'),
            'sort' => $this->request->query('sort'),
            'dir' => $this->request->query('dir'),
        ];
    }
}

