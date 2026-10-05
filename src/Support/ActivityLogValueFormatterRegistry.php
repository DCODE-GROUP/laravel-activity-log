<?php

namespace Dcodegroup\ActivityLog\Support;

use Dcodegroup\ActivityLog\Contracts\ActivityLogValueFormatter;
use Dcodegroup\ActivityLog\Support\ValueFormatters\FallbackValueFormatter;
use InvalidArgumentException;

class ActivityLogValueFormatterRegistry
{
    public function format(mixed $value): string
    {
        foreach ($this->formatters() as $formatter) {
            if ($formatter->supports($value)) {
                return $formatter->format($value);
            }
        }

        return (new FallbackValueFormatter)->format($value);
    }

    /**
     * @return list<ActivityLogValueFormatter>
     */
    private function formatters(): array
    {
        return collect(config('activity-log.value_formatters', []))
            ->map(function (mixed $formatter): ActivityLogValueFormatter {
                $instance = is_object($formatter) ? $formatter : app($formatter);

                if (! $instance instanceof ActivityLogValueFormatter) {
                    throw new InvalidArgumentException(sprintf(
                        'Activity log value formatters must implement %s.',
                        ActivityLogValueFormatter::class,
                    ));
                }

                return $instance;
            })
            ->values()
            ->all();
    }
}
