# Laravel Activity Log

The `dcodegroup/activity-log` package provides a simple and unified approach to track and record activity / interactions against your Laravel models (and relations). Capture changes, updates, and user interactions to enhance transparency and auditing in your application in a centralised and consistent approach.

## Installation
#### PHP
You can install the package via composer:

```bash
composer require dcodegroup/activity-log
```

Then run the install command.

```bash
php artisan activity-log:install
```

Run the migrations

```bash
php artisan migrate
```

## User Model

Add the following contract to the `User` model.

```php  

use Dcodegroup\ActivityLog\Contracts\HasActivityUser;

class User extends Authenticatable implements HasActivityUser
{
...

public function getActivityLogUserName(): string
{
    return $this->name;
}
```

#### JS

Add the following js to your `index.js` file.

```javascript
import VActivityLog from "@dcode/activity-log/resources/js/components/VActivityLog.vue";
import ActivityLogList from "@dcode/activity-log/resources/js/components/ActivityLogList.vue";
import ActivityEmail from "@dcode/activity-log/resources/js/components/ActivityEmail.vue";

app.component("VActivityLog", VActivityLog);
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);
```

In your `app.scss` file add the following

```scss
@import "activity-log/index.scss";
```

Seem to need this in `tailwind.config.js` under spacing: 

```js
spacing: {
    "3xlSpace": "96px",
    "2xlSpace": "64px",
    xlSpace: "32px",
    lgSpace: "24px",
    mdSpace: "16px",
    smSpace: "12px",
    xsSpace: "8px",
    "2xsSpace": "4px",
    "3xsSpace": "2px",
},
```

Update the vue il8n package to load additional paths

```javascript
i18n({
  // you can also change your langPath here
  // langPath: 'locales'
  additionalLangPaths: [
    "vendor/dcodegroup/activity-log/lang", // Load translations from this path too!
  ],
}),

Run the npm build (dev/prod)

```bash
npm run prod:assets
```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/activity-log.php` and adjust as needed

```php

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

    'datetime_format' => env('LARAVEL_ACTIVITY_LOG_DATETIME_FORMAT', 'd-m-Y H:ia'),
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


```

| Value           | Options | Description                                                                                 |
|-----------------|---------|---------------------------------------------------------------------------------------------|
| middleware      |         | Include a specification of what middleware this package should include.                     |
| layout_path     |         | The dot notation path to the resource/view that you would like to use for the Activity Log. |
| content_section |         | The variable in the view that will contain the output of the Activtity Log.                 |

## Usage

The package provides an endpoints which you can use. See the full list by running
```bash
php artisan route:list --name=activity-log
```

They are

[example.com/activity-logs] Which is where you will form index. This is by default protected auth middleware but you can modify in the configuration. This is where you want to link to in your admin and possibly a new window

## QueryBuilder Filters

Located in
```
src\Support\QueryBuilder\Filters\DateRangeFilter.php
src\Support\QueryBuilder\Filters\TermFilter.php
```

## Traits for activity log model

Located in
```
src\Support\Traits\ActivityLoggable.php
src\Support\Traits\LastModifiedBy.php
```

Using `<activity-log-list>` or `<v-activity-log>` to display activity log list. Pass filter as a slot if filter functionality is needed
```html
      <ActivityLogList :model-id="tender.id" :model-class="tenderModel">
        <v-filter entity="activity-logs" class="flex flex-row-reverse space-x-2 space-x-reverse"> </v-filter>
      </ActivityLogList>
```

## Usage

In order to log anything add the following trait to a model you want to log on.

```php
...
use Dcodegroup\ActivityLog\Support\Traits\ActivityLoggable;

class Order extends Model
{
    use ActivityLoggable;
    ...
}
```

# Changelog

Please see [CHANGELOG](./CHANGELOG.md) for more information about recent changes.

# Contributing

We believe in the power of collaboration! If you share our passion for pushing the boundaries of business software, feel free to contribute, report issues, or suggest improvements. Your insights make us better.


# Security

If you've found an issue related to this package that includes any security concerns; please email [security@dcodegroup.com](mailto:security@dcodegroup.com) to ensure that we can prioritise the concerns in a confidential manner.

# Credits

This project is supported & funded by [Dcode Group](https://github.com/dcodegroup) and the team - both past and present.  Special mention to:
- [Dcode Group](https://github.com/dcodegroup)
- [All Contributors](./graphs/contributors)

## About Dcode Group

Dcode Group specializes in crafting tailored software solutions utilizing the Laravel framework. Our focus lies in developing business, financial, and process-driven systems designed to support unique business operations. Leveraging packages like this one, we streamline common features/functions across projects, ensuring swift integration of broad functionalities while enhancing overall code base maintenance and management.  Find out more about 

# License


