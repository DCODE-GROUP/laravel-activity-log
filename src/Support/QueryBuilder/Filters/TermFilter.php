<?php

namespace Dcodegroup\ActivityLog\Support\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Spatie\QueryBuilder\Filters\Filter;

class TermFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->whereHas('user', function (Builder $q) use ($value) {
            if (Schema::hasColumns('user', ['username', 'first_name', 'middle_name', 'last_name'])) {
                return $q->where('username', 'like', "%$value%")
                    ->orWhere('first_name', 'like', "%$value%")
                    ->orWhere('middle_name', 'like', "%$value%")
                    ->orWhere('last_name', 'like', "%$value%");
            }
            if (Schema::hasColumn('user', 'email')) {
                return $q->where('email', 'like', "%$value%");
            }
        })
            ->where(function (Builder $q) use ($value) {
                $q->where('created_at', 'like', "%$value%")
                    ->orWhere('description', 'like', "%$value%")
                    ->orWhere('title', 'like', "%$value%");
            });

    }
}
