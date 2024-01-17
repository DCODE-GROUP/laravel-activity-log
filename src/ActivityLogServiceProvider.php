<?php

namespace Dcodegroup\ActivityLog;

use Dcodegroup\ActivityLog\Commands\InstallCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->offerPublishing();
        $this->registerRoutes();
        $this->registerResources();
        $this->registerCommands();

        Route::model(config('activity-log.binding'), config('activity-log.model'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('ACTIVITY_LOG_PATH')) {
            define('ACTIVITY_LOG_PATH', realpath(__DIR__.'/../'));
        }

        $this->mergeConfigFrom(ACTIVITY_LOG_PATH.'/config/activity-log.php', 'activity-log');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero oAuth.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->doesntHaveTables()) {
            $timestamp = date('Y_m_d_His', time());
            $extraTimestamp =  date('Y_m_d_His', time() + 10);
            $this->publishes([
                ACTIVITY_LOG_PATH.'/database/migrations/create_communication_logs_table.stub.php' => database_path('migrations/'.$timestamp.'_create_communication_logs_table.php'),
                ACTIVITY_LOG_PATH.'/database/migrations/create_activity_logs_table.stub.php' => database_path('migrations/'.$extraTimestamp.'_create_activity_logs_table.php'),
            ], 'activity-log-migrations');
        }

        $this->publishes([ACTIVITY_LOG_PATH.'/config/activity-log.php' => config_path('activity-log.php')], 'activity-log-config');
        $this->publishes([ACTIVITY_LOG_PATH.'/resources/sass' => resource_path('sass/activity-log')], 'activity-log-sass');
        $this->publishes([ACTIVITY_LOG_PATH.'/public' => public_path('vendor/activity-log')], ['activity-log-assets']);
    }

    private function doesntHaveTables()
    {
        return
            $this->app->environment('local') &&
            ! Schema::hasTable('communication_logs') &&
            (Schema::hasTable('migrations') && ! DB::table('migrations')->where('migration', 'like', '%create_communication_logs_table')->exists());
    }

    protected function registerResources()
    {
        $this->loadTranslationsFrom(ACTIVITY_LOG_PATH.'/resources/lang', 'activity-log-translations');
        $this->loadViewsFrom(ACTIVITY_LOG_PATH.'/resources/views', 'activity-log-views');
    }

    protected function registerRoutes()
    {
        Route::group([
            'middleware' => config('activity-log.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(ACTIVITY_LOG_PATH.'/routes/activity-log.php');
        });
    }
}
