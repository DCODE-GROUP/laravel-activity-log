<?php

namespace Dcodegroup\ActivityLog;

use Dcodegroup\ActivityLog\Commands\InstallCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->offerPublishing();
        $this->registerRoutes();
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'activity-log-translations');

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
        $this->mergeConfigFrom(__DIR__.'/config/activity-log.php', 'activity-log');
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
        $this->setupMigrations();

        $this->publishes([__DIR__.'/../config/activity-log.php' => config_path('activity-log.php')], 'activity-log-config');
        $this->publishes([__DIR__.'/../resources/sass' => resource_path('sass/activity-log')], 'activity-log-sass');
        // $this->publishes([__DIR__.'/../public' => public_path('vendor/activity-log')], ['activity-log-assets']);
        // $this->publishes([__DIR__.'/../lang' => $this->app->langPath('en/vendor/dcodegroup/activity-log')], 'activity-log-translations');
        $this->publishes([__DIR__.'/../lang' => $this->app->langPath()], 'activity-log-translations');
    }

    protected function setupMigrations()
    {
        if ($this->app->environment('local')) {
            if (! Schema::hasTable('activity_logs') && ! Schema::hasTable('communication_logs')) {
                $this->publishes([
                    __DIR__.'/../database/migrations/create_activity_logs_tables.stub.php' => $this->app->databasePath('migrations/'.date('Y_m_d_His', time()).'_create_activity_logs_tables.php'),
                ], 'activity-log-migrations');
            }
        }
    }

    protected function registerRoutes()
    {
        Route::group([
            'middleware' => config('activity-log.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/activity-log.php');
        });
    }
}
