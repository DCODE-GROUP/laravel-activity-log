<?php

namespace Dcodegroup\ActivityLog\Tests\Support\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class CommandStartingListener
{
    public function handle(CommandStarting $event): void
    {
        if ($event->command !== 'serve') {
            return;
        }

        // Create the user model stub
        config(['auth.providers.users.model', 'Workbench\App\Models\User']);

        Artisan::call('package:create-sqlite-db');
        Artisan::call('migrate:fresh');

        // Ensure there's an admin user
        $userModel = config('auth.providers.users.model');
        $admin = $userModel::where('email', 'admin@test.com')->first();
        if (! $admin) {
            $admin = new $userModel;
        }
        $admin->email = 'admin@test.com';
        $admin->password = Hash::make('password');
        $admin->name = 'Admin';
        $admin->save();

    }
}
