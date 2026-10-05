<?php

namespace Dcodegroup\ActivityLog\Tests\Fixtures;

use Dcodegroup\ActivityLog\Support\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class ValueFormattingModel extends Model
{
    use ActivityLoggable;

    protected $guarded = [];

    protected $casts = [
        'status' => StatusWithLabel::class,
        'priority' => Priority::class,
        'visibility' => Visibility::class,
        'starts_at' => 'datetime',
        'active' => 'boolean',
        'tags' => 'array',
    ];
}
