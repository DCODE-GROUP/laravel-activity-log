<?php

namespace Dcodegroup\ActivityLog\Models;

use Carbon\Carbon;
use Dcodegroup\ActivityLog\Support\Traits\LastModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property CommunicationLog|null $communicationLog
 * @property string $activitiable_type
 * @property int $activitiable_id
 * @property string $type
 * @property array $meta
 * @property string $description
 */
class ActivityLog extends Model
{
    use LastModifiedBy;
    use SoftDeletes;

    final public const TYPE_GENERAL = 'General';

    final public const TYPE_SMS = 'Sms';

    final public const TYPE_EMAIL = 'Email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activitiable_type',
        'activitiable_id',
        'communication_log_id',
        'type',
        'meta',
        'description',
        'diff',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'diff' => 'array',
        'meta' => 'array',
    ];

    public function activitiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('activity-log.user_model'), 'created_by');
    }

    public function communicationLog(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class, 'communication_log_id');
    }

    public function getAvailableTypes(): array
    {
        return [
            [
                'name' => static::TYPE_GENERAL,
            ],
            [
                'name' => static::TYPE_SMS,
            ],
            [
                'name' => static::TYPE_EMAIL,
            ],
        ];
    }
}
