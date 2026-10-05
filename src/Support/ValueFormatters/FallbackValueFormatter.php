<?php

namespace Dcodegroup\ActivityLog\Support\ValueFormatters;

use Coduo\ToString\StringConverter;
use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;

class FallbackValueFormatter implements ActivityLogValueFormatter
{
    public function supports(mixed $value): bool
    {
        return true;
    }

    public function format(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value === null) {
            return '';
        }

        return (string) new StringConverter($value);
    }
}
