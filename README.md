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

This will publish the configuration file and the migration file.

Run the migrations

```bash
php artisan migrate
```

#### JS

Include this built file to your layouts:

```
<script type="text/javascript" src="/vendor/activity-log/index.js" defer></script>
```

#### SCSS

There is a new generated file under `public/vendor/activity-log/index.css`. You must use this file in your main scss file

Run the npm build (dev/prod)

```bash
npm run dev
```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/activity-log.php` and adjust as needed

```
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
    'user_table' => 'users', // eg 'users',
    'filter_builder_path' => '', //eg 'FilterBuilder class: App\Support\QueryBuilder\Filters\FilterBuilder'
    'open_modal_event' => 'openModal', // eg 'openModal'
    'reload_event' => 'getActivities',
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

## Override supports

Located in
```
src\Support\DateRangeFilter.php
src\Support\TermFilter.php
```

## Traits for activity log model

Located in
```
src\Http\Models\Traits\ActivityLoggable.php
```

## Using <activity-log-list> or <v-activity-log> to display activity log list. Pass filter as a slot if filter functionality is needed
```
      <ActivityLogList :model-id="tender.id" :model-class="tenderModel">
        <v-filter entity="activity-logs" class="flex flex-row-reverse space-x-2 space-x-reverse"> </v-filter>
      </ActivityLogList>
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


