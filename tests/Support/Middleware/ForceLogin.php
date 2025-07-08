<?php

namespace Dcodegroup\ActivityLog\Tests\Support\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Workbench\App\Models\User;

class ForceLogin
{
    public function handle($request, Closure $next)
    {

        if (! Auth::check()) {
            Auth::loginUsingId(User::first()->id);
        }
        \Log::info('ForceLogin ran', ['user_id' => optional(auth()->user())->id]);

        return $next($request);
    }
}
