<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ExpoNotification;
use Illuminate\Console\Command;

class SendTestExpoNotification extends Command
{
    protected $signature = 'expo:send-test {--user= : The user ID to send to (default: all users with expo token)}';

    protected $description = 'Send a test Expo push notification to users with an Expo token.';

    public function handle(): void
    {
        $query = User::query()->whereNotNull('expo_token');

        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('No users with Expo tokens found.');

            return;
        }

        $this->info("Sending test notification to {$users->count()} user(s)...");

        foreach ($users as $user) {
            /** @var \App\Models\User $user */
            $this->line("  → {$user->email}");

            try {
                $user->notify(new ExpoNotification(
                    title: 'Test Notification',
                    body: 'This is a test push notification from JohnFit.',
                ));
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
            }
        }

        $this->info('Done.');
    }
}
