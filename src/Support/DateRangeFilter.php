<?php

namespace Dcodegroup\ActivityLog\Support;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DateRangeFilter implements Filter
{
    protected $propertyOverride = null;

    public function __construct($propertyOverride = null)
    {
        $this->propertyOverride = $propertyOverride;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        // @phpstan-ignore-next-line
        return $query->whereBetween($this->propertyOverride ?? $property, $value);
    }
}
