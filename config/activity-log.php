<?php

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Dcodegroup\ActivityLog\Support\ValueFormatters\ArrayValueFormatter;
use Dcodegroup\ActivityLog\Support\ValueFormatters\BooleanValueFormatter;
use Dcodegroup\ActivityLog\Support\ValueFormatters\DateValueFormatter;
use Dcodegroup\ActivityLog\Support\ValueFormatters\EnumValueFormatter;
use Dcodegroup\ActivityLog\Support\ValueFormatters\FallbackValueFormatter;

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
    | eg '/activity-logs'
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
     | Attachments
     |--------------------------------------------------------------------------
     |
     | Configure these values when comments can contain attachments.
     | attachment_url may contain an "{attachment}" placeholder. If it does
     | not, the attachment id is appended to the configured URL.
     |
    */

    'attachment_url' => env('LARAVEL_ACTIVITY_LOG_ATTACHMENT_URL'),
    'attachment_model' => null,

    /*
     |--------------------------------------------------------------------------
     | Formatting
     |--------------------------------------------------------------------------
     |
     | Configuration here is for display configuration
     |
    */

    'timedate_format' => env('LARAVEL_ACTIVITY_LOG_TIMEDATE_FORMAT', 'H:ia j M Y '),
    'datetime_format' => env('LARAVEL_ACTIVITY_LOG_DATETIME_FORMAT', 'j M Y H:ia'),
    'date_format' => env('LARAVEL_ACTIVITY_LOG_DATE_FORMAT', 'j.m.Y'),

    /*
     |--------------------------------------------------------------------------
     | Value formatters
     |--------------------------------------------------------------------------
     |
     | Ordered handlers used when an attribute is written to the activity log.
     | The first formatter whose supports() method returns true formats the value.
     |
     | EnumValueFormatter: label() when the enum defines it, otherwise the
     | backed value, or the case name for a unit enum.
     | DateValueFormatter: date_format when the time is 00:00:00, otherwise
     | datetime_format. Applies to DateTimeInterface values (including Carbon).
     | BooleanValueFormatter: "Yes" / "No". Only actual booleans, not 0/1.
     | ArrayValueFormatter: each item is formatted by this same list, then
     | joined with "|".
     | FallbackValueFormatter: strings are unchanged. Null becomes an empty
     | string. Every other value uses Coduo\ToString\StringConverter, which
     | falls back to the class name when __toString is missing.
     |
     | Prepend or replace a class here to change formatting without forking.
     | Keep the fallback last: its supports() method always returns true.
     | Use activityLogFieldFormatters() for per-field domain mapping.
     |
     */

    'value_formatters' => [
        EnumValueFormatter::class,
        DateValueFormatter::class,
        BooleanValueFormatter::class,
        ArrayValueFormatter::class,
        FallbackValueFormatter::class,
    ],

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
    'user_model' => null,
    'user_table' => env('LARAVEL_ACTIVITY_LOG_USERS_TABLE', 'users'),
    'user_search_term' => env('LARAVEL_ACTIVITY_LOG_USER_SEARCH_TERM', ['email']),
    'user_search' => env('LARAVEL_ACTIVITY_LOG_USER_SEARCH', 'full_name'),
    'user_search_relationship' => env('LARAVEL_ACTIVITY_LOG_USER_SEARCH_RELATIONSHIP', []),
    'comment_email_template' => env('LARAVEL_ACTIVITY_LOG_COMMENT_EMAIL_TEMPLATE', 'activity-log::mail.comment-notification'),
    'filter_mention_user_role' => env('LARAVEL_ACTIVITY_LOG_FILTER_MENTION_USER_ROLE', ''),

    /*
     |--------------------------------------------------------------------------
     | Communication log
     |--------------------------------------------------------------------------
     |
     |
    */

    'communication_log_model' => CommunicationLog::class,
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

    /*
     |--------------------------------------------------------------------------
     | Email Queue Names
     |--------------------------------------------------------------------------
     |
    */

    'queue_name' => env('LARAVEL_ACTIVITY_LOG_QUEUE_NAME', 'default'),
];
