<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $to
 * @property string $type
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $subject
 * @property string|null $content
 */
class CommunicationLog extends Model
{
    final public const TYPE_SMS = 'Sms';

    final public const TYPE_EMAIL = 'Email';

    final public const ICON_TYPE_MAP = [
        self::TYPE_SMS => 'ChatBubbleBottomCenterText',
        self::TYPE_EMAIL => 'EnvelopeIcon',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'to',
        'cc',
        'bcc',
        'subject',
        'content',
    ];
}
