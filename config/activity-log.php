<?php

use Dcodegroup\ActivityLog\Models\ActivityLog;

return [
    'middleware' => ['web', 'auth'],
    'layout_path' => 'layouts.app',
    'content_section' => 'content',
    'route_path' => 'activity-logs', // eg 'api/generic/activity-logs',
    'route_name' => 'activity-logs', // eg 'api.generic.activity-logs',
    'binding' => 'activity-logs', // eg 'activity-logs',
    'model' => ActivityLog::class, // eg 'ActivityLog',
    'datetime_format' => 'd-m-Y H:ia',
    'date_format' => 'd.m.Y',
    'default_filter_pagination' => 50,
    'user_model' => '', // eg 'User',
    'filter_builder_path' => '', //eg 'FilterBuilder class: App\Support\QueryBuilder\Filters\FilterBuilder'
    'open_modal_event' => 'openModal', // eg 'openModal'
    'reload_event' => 'getActivities',
];
