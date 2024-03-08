<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $to
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $subject
 * @property string|null $content
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
