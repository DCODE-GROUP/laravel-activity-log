<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 */
class CommunicationLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'to',
        'cc',
        'bcc',
        'subject',
        'content',
    ];
}
