<?php

namespace App\Console\Commands;

use App\Models\QuoteSetting;
use App\Models\User;
use App\Notifications\ExpoNotification;
use Illuminate\Console\Command;

class SendMorningQuote extends Command
{
    protected $signature = 'quotes:send-morning';

    protected $description = 'Send the daily morning quote notification to all users with an Expo token.';

    public function handle(): void
    {
        $setting = QuoteSetting::instance();
        $quote = $setting->resolveNextQuote();

        if (! $quote) {
            $this->warn('No quotes found in the database.');

            return;
        }

        $users = User::query()->whereNotNull('expo_token')->get();

        if ($users->isEmpty()) {
            $this->warn('No users with Expo tokens found.');

            // Still advance the pointer so the sequence stays correct
            $setting->update([
                'last_sent_quote_id' => $quote->id,
                'next_quote_id' => null,
            ]);

            return;
        }

        $this->info("Sending morning quote (#{$quote->id}) to {$users->count()} user(s)...");

        foreach ($users as $user) {
            /** @var \App\Models\User $user */
            try {
                $user->notify(new ExpoNotification(
                    title: '🌅 Morning Quote',
                    body: $quote->quote,
                    data: ['type' => 'morning_quote', 'quote_id' => $quote->id],
                ));
            } catch (\Exception $e) {
                $this->error("  Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $setting->update([
            'last_sent_quote_id' => $quote->id,
            'next_quote_id' => null,
        ]);

        $this->info('Done.');
    }
}
