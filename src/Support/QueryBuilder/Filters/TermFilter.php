<?php

namespace Dcodegroup\ActivityLog\Support\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class TermFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->whereHas('user', function (Builder $q) use ($value) {
            return $q->where('username', 'like', "%$value%")
                ->orWhere('first_name', 'like', "%$value%")
                ->orWhere('last_name', 'like', "%$value%");
        })
            ->orWhere('created_at', 'like', "%$value%")
            ->orWhere('description', 'like', "%$value%");
    }
}
