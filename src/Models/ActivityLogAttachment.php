<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $activity_log_id
 * @property int $attachment_id
 * @property-read ActivityLog $activityLog
 * @property-read Model|null $attachment
 */
class ActivityLogAttachment extends Model
{
    protected $fillable = [
        'activity_log_id',
        'attachment_id',
    ];

    public function activityLog(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'activity_log_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(config('activity-log.attachment_model'), 'attachment_id');
    }
}
