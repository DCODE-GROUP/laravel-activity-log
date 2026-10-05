<?php

namespace Dcodegroup\ActivityLog\Support\ValueFormatters;

use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;
use Dcodegroup\ActivityLog\Support\ActivityLogValueFormatterRegistry;

class ArrayValueFormatter implements ActivityLogValueFormatter
{
    public function supports(mixed $value): bool
    {
        return is_array($value);
    }

    public function format(mixed $value): string
    {
        if (! is_array($value)) {
            return '';
        }

        return collect($value)
            ->map(fn (mixed $item) => app(ActivityLogValueFormatterRegistry::class)->format($item))
            ->join('|');
    }
}
