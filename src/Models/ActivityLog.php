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
 * @property Carbon $updated_at
 * @property CommunicationLog|null $communicationLog
 * @property int|null $communication_log_id
 * @property string $activitiable_type
 * @property int $activitiable_id
 * @property string $type
 * @property array $meta
 * @property string $description
 * @property string $title
 */
class ActivityLog extends Model
{
    use LastModifiedBy;
    use SoftDeletes;

    final public const TYPE_DATA = 'Change of Data';

    final public const TYPE_STATUS = 'Change of Status';

    final public const TYPE_COMMENT = 'Comment';

    final public const TYPE_NOTIFICATION = 'Notification';

    final public const ICON_TYPE_MAP = [
        self::TYPE_DATA => 'PencilIcon',
        self::TYPE_STATUS => 'ArrowPathIcon',
        self::TYPE_COMMENT => 'ChatBubbleLeftEllipsisIcon',
        self::TYPE_NOTIFICATION => 'BellIcon',
    ];

    final public const COLOR_TYPE_MAP = [
        self::TYPE_DATA => 'pink',
        self::TYPE_STATUS => 'violet',
        self::TYPE_COMMENT => 'teal',
        self::TYPE_NOTIFICATION => 'orange',
    ];

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
        'title',
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
        return collect([
            static::TYPE_DATA,
            static::TYPE_STATUS,
            static::TYPE_COMMENT,
            static::TYPE_NOTIFICATION,
        ])->map(fn (string $type) => ['name' => $type])->toArray();
    }
}
