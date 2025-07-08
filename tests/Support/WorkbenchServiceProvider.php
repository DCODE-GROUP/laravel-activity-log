<?php

namespace Dcodegroup\ActivityLog\Tests\Support;

use Dcodegroup\ActivityLog\Tests\Support\Listeners\CommandStartingListener;
use Dcodegroup\ActivityLog\Tests\Support\Middleware\ForceLogin;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Route;

class WorkbenchServiceProvider extends EventServiceProvider
{
    protected $listen = [
        CommandStarting::class => [
            CommandStartingListener::class,
        ],
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        app('router')->aliasMiddleware('force-login', ForceLogin::class);

        // Apply it globally to all routes
        app('router')->pushMiddlewareToGroup('web', 'force-login');

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        app('router')->prependMiddlewareToGroup('web', ForceLogin::class);

        $this->loadViewsFrom(__DIR__.'/resources/views', 'test');

        Route::get('/debug-middleware', function () {
            return response()->json(app('router')->getMiddlewareGroups());
        });

        Route::middleware('web')->get('/vue', function () {
            return view('test::index');
        });

        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'activity-logs')) {
                $route->middleware(['force-login', 'web']);
            }
        }
    }
}
