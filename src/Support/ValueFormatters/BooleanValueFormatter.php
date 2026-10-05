<?php

namespace Dcodegroup\ActivityLog\Support\ValueFormatters;

use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;

class BooleanValueFormatter implements ActivityLogValueFormatter
{
    public function supports(mixed $value): bool
    {
        return is_bool($value);
    }

    public function format(mixed $value): string
    {
        return $value ? 'Yes' : 'No';
    }
}
