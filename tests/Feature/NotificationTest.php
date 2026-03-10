<?php

use App\Models\SentNotification;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user']);
});

describe('Notifications API', function () {
    it('returns notifications for authenticated user', function () {
        $this->user->notify(new AdminNotification('Hello', 'Test body', 'info'));

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'notifications' => [['id', 'title', 'body', 'type', 'read', 'created_at']],
                'unread_count',
            ])
            ->assertJsonPath('unread_count', 1);
    });

    it('redirects unauthenticated users', function () {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    });

    it('marks a single notification as read', function () {
        $this->user->notify(new AdminNotification('Hello', 'Body', 'info'));

        $notificationId = $this->user->notifications->first()->id;

        $this->actingAs($this->user)
            ->patchJson(route('notifications.read', $notificationId))
            ->assertOk()
            ->assertJson(['success' => true]);

        expect(
            DatabaseNotification::find($notificationId)->read_at
        )->not->toBeNull();
    });

    it('marks all notifications as read', function () {
        $this->user->notify(new AdminNotification('First', 'Body 1', 'info'));
        $this->user->notify(new AdminNotification('Second', 'Body 2', 'success'));

        $this->actingAs($this->user)
            ->patchJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJson(['success' => true]);

        expect($this->user->fresh()->unreadNotifications->count())->toBe(0);
    });
});

describe('SentNotification & Observer', function () {
    it('dispatches notifications to all non-admin users when target_type is all', function () {
        Notification::fake();

        $otherUser = User::factory()->create(['role' => 'user']);

        SentNotification::create([
            'title' => 'Broadcast',
            'body' => 'For everyone',
            'type' => 'info',
            'target_type' => 'all',
            'target_user_ids' => null,
            'sent_by' => $this->admin->id,
        ]);

        Notification::assertSentTo([$this->user, $otherUser], AdminNotification::class);
        Notification::assertNotSentTo($this->admin, AdminNotification::class);
    });

    it('dispatches notifications only to selected users when target_type is specific', function () {
        Notification::fake();

        $otherUser = User::factory()->create(['role' => 'user']);

        SentNotification::create([
            'title' => 'Personal',
            'body' => 'Just for you',
            'type' => 'success',
            'target_type' => 'specific',
            'target_user_ids' => [$this->user->id],
            'sent_by' => $this->admin->id,
        ]);

        Notification::assertSentTo($this->user, AdminNotification::class);
        Notification::assertNotSentTo($otherUser, AdminNotification::class);
    });
});
