<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $activity_log_id
 * @property int|null $user_id
 * @property string $emoji
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Dcodegroup\ActivityLog\Models\ActivityLog $activityLog
 * @property-read mixed $user
 */
class ActivityLogReaction extends Model
{
    protected $table = 'activity_log_reactions';

    protected $fillable = [
        'activity_log_id',
        'user_id',
        'emoji',
    ];

    public function activityLog(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'activity_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('activity-log.user_model'), 'user_id');
    }
}
