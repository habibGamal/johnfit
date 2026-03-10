<?php

namespace App\Observers;

use App\Models\SentNotification;
use App\Models\User;
use App\Notifications\AdminNotification;

class SentNotificationObserver
{
    public function created(SentNotification $sentNotification): void
    {
        $notification = new AdminNotification(
            title: $sentNotification->title,
            body: $sentNotification->body,
            type: $sentNotification->type,
        );

        if ($sentNotification->target_type === 'all') {
            $users = User::query()->where('role', '!=', 'admin')->get();
        } else {
            $users = User::query()->whereIn('id', $sentNotification->target_user_ids ?? [])->get();
        }

        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
