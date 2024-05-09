<?php

namespace Dcodegroup\ActivityLog\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $to
 * @property string $type
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $subject
 * @property string|null $content
 * @property Collection $reads
 * @property Collection $views
 * @property Carbon $created_at
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

    public function reads()
    {
        return $this->hasMany(ActivityLog::class, 'communication_log_id', 'id')
            ->where('title', 'like', '% read an %');
    }

    public function views()
    {
        return $this->hasMany(ActivityLog::class, 'communication_log_id', 'id')
            ->where('title', 'like', '% view a %');
    }
}
