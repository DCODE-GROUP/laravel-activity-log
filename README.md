# Laravel Activity Log

The `dcodegroup/activity-log` package provides a simple and unified approach to track and record activity / interactions
against your Laravel models (and relations). Capture changes, updates, and user interactions to enhance transparency and
auditing in your application in a centralised and consistent approach.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dcodegroup/activity-log.svg?style=flat-square)](https://packagist.org/packages/dcodegroup/activity-log)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/DCODE-GROUP/laravel-activity-log/ci.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/DCODE-GROUP/laravel-activity-log/actions/worflows/ci.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/dcodegroup/activity-log.svg?style=flat-square)](https://packagist.org/packages/dcodegroup/activity-log)

## Update note

Since `version 1.1.1` we are no longer need to use observers to listen for changes from the
model. `bootActivityLoggable` in `ActivityLoggable` trait solved that. Make sure to remove duplicate observers before
updating

## Installation

#### Add the following to your package.json file:

```bash
"dependencies": {
   ...
    "floating-vue": "^2.0.0-beta.1",
    "vue-markdown-render": "^2.1.1",
    "vue-mention": "^2.0.0-alpha.3"
}
```

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

public function getActivityLogEmail(): string
{
    return $this->email;
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
app.directive("click-outside", clickOutside);
```

In your `app.scss` file add the following

```scss
@import "activity-log/index.scss";
@import "floating-vue/dist/style.css";
```

Seem to need this in `tailwind.config.js` under spacing:

```js
spacing: {
  "3xlSpace"
:
  "96px",
    "2xlSpace"
:
  "64px",
    xlSpace
:
  "32px",
    lgSpace
:
  "24px",
    mdSpace
:
  "16px",
    smSpace
:
  "12px",
    xsSpace
:
  "8px",
    "2xsSpace"
:
  "4px",
    "3xsSpace"
:
  "2px",
}
,
```

Update the module exports under content:

```js
content: [
  ...
    "./vendor/dcodegroup/**/*.{blade.php,vue,js,ts}",
  ...
],
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
```

Run the npm build (dev/prod)

```bash
npm run prod:assets
```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file
at `config/activity-log.php` and adjust as needed

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

[example.com/activity-logs] Which is where you will form index. This is by default protected auth middleware but you can
modify in the configuration. This is where you want to link to in your admin and possibly a new window

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

# ActivityLoggable Trait

The `ActivityLoggable` trait provides functionality for logging activities and communication logs related to a model.

Methods
-------

* **`logCreate(): void`**: Automatically create activity log every time a new model is created. (support from version
  1.1.1)
* **`logUpdate(): void`**: Automatically create activity log every time when model is updated. (support from version
  1.1.1)
* **`logDelete(): void`**: Automatically create activity log every time when model is deleted. (support from version
  1.1.1)
* **`getActivityLogEmails(): array`**: Get the emails associated with activity logs.
* **`activities(): Collection`**: Get the collection of activities associated with the model.
* **`modelRelation(): Collection`**: Get the relationship between the model column with the related
  table. `modelRelation` also has the effect of limiting logging to defined columns instead of logging all changes from
  the model when you declare `getModelChangesJson(true)`

Example of define modelRelation via model using `ActivityLoggable`

```php
 public function modelRelation(): Collection
    {
        return collect([
            'account_id' => collect([               // column change in model
                'label' => 'Account',               // attribute label display in activity log description
                'modelClass' => Account::class,     // relationship model
                'modelKey' => 'name',               // columns display instead 
            ]),
          ......
          ])

```

when declared like this instead of activity log shows like this. `account_id: 1 -> 20` The result will return like
this: `Account: Alison Cahill -> Annie Pollock`.

* **`getModelChanges(?array $modelChangesJson = null): string`**: Get the model changes as a formatted string.
* **`getModelChangesJson(bool $allowCustomAttribute = false): array`**: Get the model changes as an array of JSON.
  If `$allowCustomAttribute` =  `true` If we want to limit the storage of fields defined in modelRelation; `false` : If
  we want to storage all model change
* **`createActivityLog(array $description): ActivityLog`**: Create a new activity log.

Example of define activity log via model using `ActivityLoggable`

```php
// Creating an activity log
$activityLog = $model->createActivityLog([
'type' => \Dcodegroup\ActivityLog\Models\ActivityLog::TYPE_DATA // if type is null default type will be TYPE_DATA, we support 3 other types: TYPE_STATUS, TYPE_COMMENT, TYPE_NOTIFICATION 
'title' => 'Updated profile information',
'description' => 'Updated user profile information',
// Additional custom fields as needed
'communication_log_id' => '' // required when type = TYPE_NOTIFICATION to link activity log with communication log
]);
```

If you have a user case where you want the log messages to be logged against another model, Example. You have an `Order`
model and you want the `OrderItem` models to be recorded against the `Order`. Then do as below.

with the `OrderItem` model add the method `targetModel`

```php

class OrderItem extends Model
{
    ...
    public function targetModel(): self|Model
    {
        return $this->order;
    }
    
}

```

You can use a custom formatter for fields in your model by using the `activityLogFieldFormatters` method.

example. Add the following to the model

```php
...
    public function activityLogFieldFormatters(): collection
    {
        return collect([
            'price' => fn ($value) => Number::currency(($value / 100), 'AUD'),
        ]);
    }
```

`price` is the key for the field.
Right hand side should be a closure than can then be used for format the value that will be present.

*
    *
*`createCommunicationLog(array $data, string $to, string $content, string $type = CommunicationLog::TYPE_EMAIL): CommunicationLog`
**: Create a new communication log.

Example of define Communication log via model using `ActivityLoggable`

```php
// Creating a communication log
$communicationLog = $model->createCommunicationLog([
'type' => 
'cc' => ['cc@example.com'],
'bcc' => ['bcc@example.com'],
'subject' => 'Subject of the email',
], 'to@example.com', 'Content of the email');
```

## Traits for activity log mailable to support tracking read email

Located in

```bash
src\Support\Traits\ReadMailableTrait.php
```

Using `<activity-log-list>` or `<v-activity-log>` to display activity log list. Pass filter as a slot if filter
functionality is needed

```html

<ActivityLogList :model-id="tender.id" :model-class="tenderModel">
    <v-filter entity="activity-logs" class="flex flex-row-reverse space-x-2 space-x-reverse"></v-filter>
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

In addition, we can add activity log wherever we want the model

```php
    $model->createActivityLog([
            'type' => \Dcodegroup\ActivityLog\Models\ActivityLog::TYPE_COMMENT // if type is null default type will be TYPE_DATA, we support 3 other types: TYPE_STATUS, TYPE_COMMENT, TYPE_NOTIFICATION
            'title' => 'left a comment.',
            'description' => 'left a comment',
        ]);
```

Add content markdown email to support comment-notification.Located in

```
resources\views\mail\comment-notification.blade.php
```

# Changelog

Please see [CHANGELOG](./CHANGELOG.md) for more information about recent changes.

# Contributing

We believe in the power of collaboration! If you share our passion for pushing the boundaries of business software, feel
free to contribute, report issues, or suggest improvements. Your insights make us better.

# Security

If you've found an issue related to this package that includes any security concerns; please
email [security@dcodegroup.com](mailto:security@dcodegroup.com) to ensure that we can prioritise the concerns in a
confidential manner.

# Credits

This project is supported & funded by [Dcode Group](https://github.com/dcodegroup) and the team - both past and present.
Special mention to:

- [Dcode Group](https://github.com/dcodegroup)
- [All Contributors](./graphs/contributors)

## About Dcode Group

Dcode Group specializes in crafting tailored software solutions utilizing the Laravel framework. Our focus lies in
developing business, financial, and process-driven systems designed to support unique business operations. Leveraging
packages like this one, we streamline common features/functions across projects, ensuring swift integration of broad
functionalities while enhancing overall code base maintenance and management. Find out more about

# License


