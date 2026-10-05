<?php

namespace Dcodegroup\ActivityLog\Support\ValueFormatters;

use BackedEnum;
use Coduo\ToString\StringConverter;
use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;
use Stringable;
use UnitEnum;

class EnumValueFormatter implements ActivityLogValueFormatter
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

        if (method_exists($value, 'label')) {
            return $this->stringify($value->label());
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return $value->name;
    }

    private function stringify(mixed $label): string
    {
        if (is_string($label)) {
            return $label;
        }

        if (is_scalar($label) || $label instanceof Stringable) {
            return (string) $label;
        }

        return (string) new StringConverter($label);
    }
}
