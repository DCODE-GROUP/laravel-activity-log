<?php

namespace Dcodegroup\ActivityLog\Events;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilterEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Builder $query, public $model) {}
}
