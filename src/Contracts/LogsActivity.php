<?php

namespace Dcodegroup\ActivityLog\Contracts;

use Dcodegroup\ActivityLog\Models\ActivityLog;

interface LogsActivity
{
    /**
     * @param  array<string, mixed>  $description
     */
    public function createActivityLog(array $description): ActivityLog;
}
