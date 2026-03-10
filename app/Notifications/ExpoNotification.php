<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Expo\ExpoMessage;

class ExpoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->expo_token) {
            $channels[] = 'expo';
        }

        return $channels;
    }

    public function toExpo(object $notifiable): ExpoMessage
    {
        $message = ExpoMessage::create()
            ->title($this->title)
            ->body($this->body)
            ->priority('high')
            ->playSound();

        if ($this->data !== []) {
            $message->data($this->data);
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->data['type'] ?? 'info',
            'data' => $this->data,
        ];
    }
}
