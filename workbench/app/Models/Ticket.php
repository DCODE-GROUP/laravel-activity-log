<?php

namespace Workbench\App\Models;

use Dcodegroup\ActivityLog\Support\Traits\ActivityLoggable;

class Ticket extends \Illuminate\Database\Eloquent\Model
{
    use ActivityLoggable;

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
    ];
}
