<?php

namespace Dcodegroup\ActivityLog\Contracts;

interface ActivityLogValueFormatter
{
    public function supports(mixed $value): bool;

    public function format(mixed $value): string;
}
