<?php

use Dcodegroup\ActivityLog\Models\ActivityLog;

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | What middleware should the package apply.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | Here you can configure the route paths and route name variables.
    |
    | What should the route path for the activity log be
    | eg 'api/generic/activity-logs'
    |
    | What should the route name for the activity log be
    | eg eg 'api.generic.activity-logs',
    */

    'route_path' => env('LARAVEL_ACTIVITY_LOG_ROUTE_PATH', 'activity-logs'),
    'route_name' => env('LARAVEL_ACTIVITY_LOG_ROUTE_NAME', 'activity-logs'),

    /*
    |--------------------------------------------------------------------------
    | Model and Binding
    |--------------------------------------------------------------------------
    |
    | binding - eg 'activity-logs'
    | model - eg 'ActivityLog'
    |
   */

    'binding' => env('LARAVEL_ACTIVITY_LOG_MODEL_BINDING', 'activity-logs'),
    'activity_log_model' => ActivityLog::class,

    /*
     |--------------------------------------------------------------------------
     | Formatting
     |--------------------------------------------------------------------------
     |
     | Configuration here is for display configuration
     |
    */

    'datetime_format' => env('LARAVEL_ACTIVITY_LOG_DATETIME_FORMAT', 'd M Y H:ia'),
    'date_format' => env('LARAVEL_ACTIVITY_LOG_DATE_FORMAT', 'd.m.Y'),

    /*
     |--------------------------------------------------------------------------
     | Pagination
     |--------------------------------------------------------------------------
     |
     | Configuration here is for pagination
     |
    */

    'default_filter_pagination' => env('LARAVEL_ACTIVITY_LOG_PAGINATION', 50),

    /*
     |--------------------------------------------------------------------------
     | User
     |--------------------------------------------------------------------------
     |
     | Configuration here is for the user model and table
     | eg 'User'
    */

    'user_relationship' => env('LARAVEL_ACTIVITY_LOG_USER_RELATIONSHIP', 'user'),
    'user_model' => \App\Models\User::class,
    'user_table' => env('LARAVEL_ACTIVITY_LOG_USERS_TABLE', 'users'),
    'user_search_term' => env('LARAVEL_ACTIVITY_LOG_USER_SEARCH_TERM', 'email'),

    /*
     |--------------------------------------------------------------------------
     | Communication log
     |--------------------------------------------------------------------------
     |
     |
    */

    'communication_log_model' => \Dcodegroup\ActivityLog\Models\CommunicationLog::class,
    'communication_log_table' => env('LARAVEL_ACTIVITY_LOG_COMMUNICATION_LOG_TABLE', 'communication_logs'),
    'communication_log_relationship' => env('LARAVEL_ACTIVITY_LOG_COMMUNICATION_LOG_RELATIONSHIP', 'communicationLog'),

    /*
     |--------------------------------------------------------------------------
     | Filter Builder
     |--------------------------------------------------------------------------
     |
     | Configuration here is for the filter builder
     | eg 'FilterBuilder class: App\Support\QueryBuilder\Filters\FilterBuilder'
    */

    'filter_builder_path' => env('LARAVEL_ACTIVITY_LOG_FILTER_BUILDER_PATH', ''),

    /*
     |--------------------------------------------------------------------------
     | Events
     |--------------------------------------------------------------------------
     |
     | Configuration here is for the events
     | eg 'open_modal_event' => 'openModal'
    */

    'open_modal_event' => env('LARAVEL_ACTIVITY_LOG_EVENT_OPEN_MODEL', 'openModal'),
    'reload_event' => env('LARAVEL_ACTIVITY_LOG_EVENT_RELOAD', 'getActivities'),
];
