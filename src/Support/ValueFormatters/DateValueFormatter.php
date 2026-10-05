<?php

namespace Dcodegroup\ActivityLog\Support\ValueFormatters;

use DateTimeInterface;
use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;

class DateValueFormatter implements ActivityLogValueFormatter
{
    public function supports(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function format(mixed $value): string
    {
        if (! $value instanceof DateTimeInterface) {
            return '';
        }

        $format = $value->format('H:i:s') === '00:00:00'
            ? config('activity-log.date_format', 'j.m.Y')
            : config('activity-log.datetime_format', 'j M Y H:ia');

        return $value->format((string) $format);
    }
}
