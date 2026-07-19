<?php

namespace Dcodegroup\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
