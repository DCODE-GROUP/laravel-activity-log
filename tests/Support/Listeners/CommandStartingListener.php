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

        $result = Artisan::call('package:create-sqlite-db');
        $result = Artisan::call('migrate:fresh');

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

        // Create a ticket for the admin user
        $ticketModel = 'Workbench\App\Models\Ticket';
        $ticket = new $ticketModel;
        $ticket->title = 'Test Ticket';
        $ticket->description = 'This is a test ticket created by the CommandStartingListener.';
        $ticket->status = 'open';
        $ticket->user_id = $admin->id;
        $ticket->save();

    }
}
