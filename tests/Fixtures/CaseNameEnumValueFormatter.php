<?php

namespace Dcodegroup\ActivityLog\Tests\Fixtures;

use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;
use UnitEnum;

class CaseNameEnumValueFormatter implements ActivityLogValueFormatter
{
    public function supports(mixed $value): bool
    {
        return $value instanceof UnitEnum;
    }

    public function format(mixed $value): string
    {
        if (! $value instanceof UnitEnum) {
            return '';
        }

        return 'case:'.$value->name;
    }
}
