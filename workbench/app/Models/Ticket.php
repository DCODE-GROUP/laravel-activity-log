<?php

namespace Workbench\App\Models;

use Dcodegroup\ActivityLog\Support\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use ActivityLoggable;

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
    ];
}
