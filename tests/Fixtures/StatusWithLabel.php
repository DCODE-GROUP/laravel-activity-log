<?php

namespace Dcodegroup\ActivityLog\Tests\Fixtures;

enum StatusWithLabel: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open ticket',
            self::Closed => 'Closed ticket',
        };
    }
}
