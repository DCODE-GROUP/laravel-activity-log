<?php

namespace Dcodegroup\ActivityLog\Models;

use App\Support\Traits\LastModifiedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property CommunicationLog|null $communicationLog
 */
class ActivityLog extends Model
{
    use LastModifiedBy;
    use SoftDeletes;

    const TYPE_GENERAL = 'General';

    const TYPE_SMS = 'Sms';

    const TYPE_EMAIL = 'Email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
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

    public static function getAvailableTypes(): array
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
