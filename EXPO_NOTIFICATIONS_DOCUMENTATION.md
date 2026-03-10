# Expo Push Notifications System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Installation & Setup](#installation--setup)
4. [Database Schema](#database-schema)
5. [User Model Integration](#user-model-integration)
6. [Authentication Flow](#authentication-flow)
7. [Notification System](#notification-system)
8. [Jobs & Queue System](#jobs--queue-system)
9. [Filament Admin Integration](#filament-admin-integration)
10. [Testing](#testing)
11. [Workflow Diagrams](#workflow-diagrams)
12. [Key Files Reference](#key-files-reference)
13. [Best Practices](#best-practices)
14. [Troubleshooting](#troubleshooting)

---

## Overview

This project implements a comprehensive push notification system using **Expo Push Notifications** for mobile app users. The system is built on top of Laravel's notification infrastructure and integrates seamlessly with the multi-tenant architecture.

### Key Features
- ✅ Automatic token registration during authentication
- ✅ Order status change notifications
- ✅ Bulk notification broadcasting
- ✅ Queue-based asynchronous delivery
- ✅ Filament admin interface for managing bulk notifications
- ✅ Multi-tenant support
- ✅ Notification history tracking
- ✅ Scheduled notifications support
- ✅ Custom data payload support

### Technology Stack
- **Package**: `laravel-notification-channels/expo` (v2.1)
- **Queue System**: Laravel Queues
- **Admin Panel**: FilamentPHP v4
- **Multi-tenancy**: Stancl Tenancy

---

## Architecture

### System Components

```
┌─────────────────┐
│  Mobile App     │
│  (Expo/React    │
│   Native)       │
└────────┬────────┘
         │ Sends expo_token
         ▼
┌─────────────────────────────────────────┐
│         Authentication Layer            │
│  - Login                                │
│  - Register                             │
│  - Google OAuth                         │
└────────┬────────────────────────────────┘
         │ Stores token
         ▼
┌─────────────────┐
│   User Model    │
│  (expo_token)   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│       Notification Triggers             │
│  - Order Events (Observer)              │
│  - Bulk Notifications (Filament)        │
│  - Manual Commands                      │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  Queue Jobs     │
│  (Async)        │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│     Notification Classes                │
│  - OrderStatusNotification              │
│  - BulkUserNotification                 │
│  - TestExpoNotification                 │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  Expo Service   │
│  (HTTP API)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Mobile Device  │
│  (Push Notif)   │
└─────────────────┘
```

---

## Installation & Setup

### 1. Package Installation

The Expo notification channel is installed via Composer:

```json
{
    "require": {
        "laravel-notification-channels/expo": "^2.1"
    }
}
```

**Installation Command:**
```bash
composer require laravel-notification-channels/expo
```

### 2. Service Provider Registration

The package's service provider is auto-discovered and registered in:
- `bootstrap/cache/services.php`

```php
38 => 'NotificationChannels\\Expo\\ExpoServiceProvider',
```

### 3. Configuration

No additional configuration files are required. The package works out of the box with Laravel's notification system.

### 4. Environment Variables

No specific environment variables are required for the Expo notification channel. The package connects directly to Expo's push notification service.

---

## Database Schema

### 1. User Table Migration

**File**: `database/migrations/tenant/2025_12_07_184627_add_expo_token_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('expo_token')->nullable()->after('remember_token');
});
```

**Purpose**: Stores the Expo push token received from the mobile app.

### 2. User Notifications Table

**File**: `database/migrations/tenant/2026_02_01_115235_create_user_notifications_table.php`

```php
Schema::create('user_notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('body');
    $table->json('data')->nullable();
    $table->boolean('read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->string('type')->default('general'); // general, order, promo, etc.
    $table->timestamps();

    $table->index(['user_id', 'read']);
    $table->index(['user_id', 'created_at']);
});
```

**Purpose**: Tracks all notifications sent to users for notification history and in-app display.

### 3. Bulk Notifications Table

**File**: `database/migrations/tenant/2026_01_31_171304_create_bulk_notifications_table.php`

```php
Schema::create('bulk_notifications', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->json('data')->nullable();
    $table->string('status')->default('draft'); // draft, scheduled, sending, sent, failed
    $table->json('target_user_ids')->nullable(); // null means all users with expo_token
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->integer('total_recipients')->default(0);
    $table->integer('successful_sends')->default(0);
    $table->integer('failed_sends')->default(0);
    $table->timestamps();
});
```

**Purpose**: Manages bulk notification campaigns with scheduling and delivery tracking.

---

## User Model Integration

**File**: `app/Models/User.php`

### 1. Imports

```php
use NotificationChannels\Expo\ExpoPushToken;
use Illuminate\Notifications\Notifiable;
```

### 2. Fillable Field

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'google_id',
    'avatar',
    'email_verified_at',
    'is_admin',
    'expo_token', // ✅ Allows mass assignment
];
```

### 3. Routing Notification Method

```php
public function routeNotificationForExpo(): ?ExpoPushToken
{
    return $this->expo_token;
}
```

**Purpose**: Tells Laravel's notification system where to find the user's Expo push token.

### 4. Casting

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'expo_token' => ExpoPushToken::class, // ✅ Automatic validation & casting
        // ... other casts
    ];
}
```

**ExpoPushToken Cast Benefits:**
- Validates token format automatically
- Ensures tokens are in the correct Expo format: `ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]`
- Type-safe token handling

### 5. Relationships

```php
public function notifications(): HasMany
{
    return $this->hasMany(UserNotification::class);
}

public function unreadNotifications(): HasMany
{
    return $this->hasMany(UserNotification::class)->where('read', false);
}
```

---

## Authentication Flow

The system captures and stores the Expo push token during various authentication flows.

### 1. Registration Flow

**File**: `app/Http/Controllers/Auth/RegisteredUserController.php`

```php
public function store(Request $request, CartService $cartService): RedirectResponse
{
    // ... user creation logic
    
    // ✅ Expo Token Registration
    if ($request->filled('expo_token')) {
        $validator = Validator::make($request->only('expo_token'), [
            'expo_token' => ExpoPushToken::rule(),
        ]);

        if ($validator->passes()) {
            $user->update(['expo_token' => $request->expo_token]);
        }
    }

    return redirect(route('home', absolute: false));
}
```

**Mobile App Request Example:**
```javascript
// React Native / Expo
injectedJavaScriptBeforeContentLoaded={`
                    window.pushToken = "${expoPushToken}";
                    true;
                    `}
                    
```

### 2. Login Flow

**File**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

```php
public function store(LoginRequest $request, CartService $cartService): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();
    
    // Sync guest cart to authenticated user
    $cartService->syncGuestCartToUser($request->user());

    // ✅ Update Expo Token on Login
    if ($request->filled('expo_token')) {
        $request->user()->update(['expo_token' => $request->expo_token]);
    }

    return redirect()->intended(route('home', absolute: false));
}
```

**Note**: Token validation is commented out in production to avoid blocking logins if token format is invalid. The mobile app should ensure valid tokens are sent.

### 3. Google OAuth Flow

**File**: `app/Http/Controllers/Auth/GoogleAuthController.php`

#### Web OAuth Callback
```php
public function callback(CartService $cartService): RedirectResponse
{
    // ... Google user authentication
    
    Auth::login($user, true);
    $cartService->syncGuestCartToUser($user);

    // ✅ Handle Session-Stored Token
    if (session()->has('expo_token')) {
        $token = session('expo_token');
        $validator = Validator::make(['expo_token' => $token], [
            'expo_token' => ExpoPushToken::rule(),
        ]);

        if ($validator->passes()) {
            $user->update(['expo_token' => $token]);
        }
        session()->forget('expo_token');
    }

    return redirect()->intended(route('home', absolute: false));
}
```

**Flow:**
1. Mobile app initiates OAuth: `/auth/google?expo_token=ExponentPushToken[xxx]`
2. Token stored in session
3. After Google callback, token retrieved and saved to user

#### Native App OAuth (Access Token)
```php
public function handleNativeCallback(Request $request): RedirectResponse
{
    $accessToken = $request->input('access_token');
    
    // Verify token via Google's API using Socialite
    $googleUser = Socialite::driver('google')->userFromToken($accessToken);
    
    // ... user creation/update
    
    Auth::login($user, true);

    // ✅ Direct Token Storage (Native Flow)
    if ($request->has('expo_token')) {
        $token = $request->input('expo_token');
        $validator = Validator::make(['expo_token' => $token], [
            'expo_token' => ExpoPushToken::rule(),
        ]);

        if ($validator->passes()) {
            $user->update(['expo_token' => $token]);
        }
    }

    return redirect()->intended(route('home', absolute: false));
}
```

### 4. Token Validation

The `ExpoPushToken::rule()` validates the token format:
- Must start with `ExponentPushToken[`
- Must contain a valid token string
- Must end with `]`

**Example Valid Token:**
```
ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]
```

---

## Notification System

### 1. Order Status Notifications

**File**: `app/Notifications/OrderStatusNotification.php`

```php
final class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * Delivery channels - only Expo push notifications
     */
    public function via(object $notifiable): array
    {
        return ['expo'];
    }

    /**
     * Build the Expo notification message
     */
    public function toExpo(object $notifiable): ExpoMessage
    {
        $title = $this->getTitle();
        $body = $this->getBody();
        $data = [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status->value,
        ];

        // ✅ Store notification in database for history
        UserNotification::create([
            'user_id' => $notifiable->id,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'type' => 'order',
        ]);

        return ExpoMessage::create()
            ->title($title)
            ->body($body)
            ->data($data)
            ->priority('high')
            ->playSound();
    }

    /**
     * Localized title based on order status (Arabic)
     */
    protected function getTitle(): string
    {
        return match ($this->order->status) {
            OrderStatus::PENDING => 'تم استلام طلبك',
            OrderStatus::PREPARING => 'جاري تحضير طلبك',
            OrderStatus::OUT_FOR_DELIVERY => 'طلبك في الطريق',
            OrderStatus::DELIVERED => 'تم توصيل طلبك',
            OrderStatus::CANCELLED => 'تم إلغاء طلبك',
        };
    }
}
```

**Features:**
- ✅ Queued notification (ShouldQueue)
- ✅ Arabic localization
- ✅ Custom data payload
- ✅ High priority delivery
- ✅ Sound enabled
- ✅ Database history tracking

### 2. Bulk User Notifications

**File**: `app/Notifications/BulkUserNotification.php`

```php
final class BulkUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BulkNotification $bulkNotification) {}

    public function via(object $notifiable): array
    {
        return ['expo'];
    }

    public function toExpo(object $notifiable): ExpoMessage
    {
        // Store in database
        UserNotification::create([
            'user_id' => $notifiable->id,
            'title' => $this->bulkNotification->title,
            'body' => $this->bulkNotification->body,
            'data' => $this->bulkNotification->data,
            'type' => 'bulk',
        ]);

        $message = ExpoMessage::create()
            ->title($this->bulkNotification->title)
            ->body($this->bulkNotification->body)
            ->priority('high')
            ->playSound();

        if ($this->bulkNotification->data) {
            $message->data($this->bulkNotification->data);
        }

        return $message;
    }
}
```

**Use Case**: Admin broadcasts promotional notifications, announcements, or updates to all users or specific user groups.

### 3. Test Notifications

**File**: `app/Notifications/TestExpoNotification.php`

```php
final class TestExpoNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['expo'];
    }

    public function toExpo($notifiable): ExpoMessage
    {
        return ExpoMessage::create()
            ->title('Test Notification')
            ->body('This is a test notification from Turbo Tenant.')
            ->priority('high')
            ->playSound();
    }
}
```

**Purpose**: Testing the notification pipeline without affecting production data.

### ExpoMessage API

The `ExpoMessage` class provides a fluent interface for building notifications:

| Method | Description | Example |
|--------|-------------|---------|
| `title(string)` | Notification title | `'Order Confirmed'` |
| `body(string)` | Notification body text | `'Your order #123 is confirmed'` |
| `data(array)` | Custom data payload | `['order_id' => 123, 'status' => 'pending']` |
| `priority(string)` | Delivery priority | `'default'`, `'normal'`, `'high'` |
| `playSound()` | Enable notification sound | ✅ |
| `badge(int)` | App badge count | `$message->badge(5)` |
| `ttl(int)` | Time-to-live (seconds) | `$message->ttl(3600)` |
| `channelId(string)` | Android notification channel | `'order_updates'` |

---

## Jobs & Queue System

### 1. Order Status Notification Job

**File**: `app/Jobs/SendOrderStatusNotificationJob.php`

```php
final class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        try {
            // ✅ Only send if user has expo_token
            if ($this->order->user && $this->order->user->expo_token) {
                $this->order->user->notify(new OrderStatusNotification($this->order));
            }
        } catch (Exception $exception) {
            logger()->error('Failed to send order status notification', [
                'order_id' => $this->order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
```

**Trigger**: Dispatched by `OrderObserver` when orders are created or updated.

### 2. Bulk Notification Job

**File**: `app/Jobs/SendBulkNotificationJob.php`

```php
final class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BulkNotification $bulkNotification) {}

    public function handle(): void
    {
        try {
            // Update status to sending
            $this->bulkNotification->update([
                'status' => BulkNotificationStatus::SENDING,
            ]);

            $users = $this->getTargetUsers();
            $successful = 0;
            $failed = 0;

            // ✅ Send to each user individually
            foreach ($users as $user) {
                try {
                    if ($user->expo_token) {
                        $user->notify(new BulkUserNotification($this->bulkNotification));
                        $successful++;
                    }
                } catch (Exception $exception) {
                    logger()->error('Failed to send bulk notification to user', [
                        'bulk_notification_id' => $this->bulkNotification->id,
                        'user_id' => $user->id,
                        'error' => $exception->getMessage(),
                    ]);
                    $failed++;
                }
            }

            // Update with results
            $this->bulkNotification->update([
                'status' => $failed === 0 
                    ? BulkNotificationStatus::SENT 
                    : BulkNotificationStatus::FAILED,
                'sent_at' => now(),
                'total_recipients' => $users->count(),
                'successful_sends' => $successful,
                'failed_sends' => $failed,
            ]);
        } catch (Exception $exception) {
            logger()->error('Failed to send bulk notification', [
                'bulk_notification_id' => $this->bulkNotification->id,
                'error' => $exception->getMessage(),
            ]);

            $this->bulkNotification->update([
                'status' => BulkNotificationStatus::FAILED,
            ]);

            throw $exception;
        }
    }

    private function getTargetUsers()
    {
        $query = User::query()->whereNotNull('expo_token');

        // ✅ Filter by specific users if targeted
        if ($this->bulkNotification->target_user_ids) {
            $query->whereIn('id', $this->bulkNotification->target_user_ids);
        }

        return $query->get();
    }
}
```

**Features:**
- ✅ Status tracking (draft → sending → sent/failed)
- ✅ Success/failure metrics
- ✅ Selective user targeting
- ✅ Individual error handling
- ✅ Comprehensive logging

### 3. Order Observer Integration

**File**: `app/Observers/OrderObserver.php`

```php
final class OrderObserver
{
    /**
     * Fired when a new order is created
     */
    public function created(Order $order): void
    {
        // ✅ Dispatch notification job
        SendOrderStatusNotificationJob::dispatch($order);
    }

    /**
     * Fired when an order is updated
     */
    public function updated(Order $order): void
    {
        // ✅ Only send notification if status changed
        if ($order->isDirty('status')) {
            SendOrderStatusNotificationJob::dispatch($order);
        }
    }
}
```

**Observer Registration**: Observers are auto-discovered in Laravel 12, or registered in `AppServiceProvider::boot()`.

---

## Filament Admin Integration

### 1. Bulk Notification Resource

**File**: `app/Filament/Resources/BulkNotifications/BulkNotificationResource.php`

The resource provides a complete CRUD interface for managing bulk notifications.

**Navigation:**
```
Admin Panel → Bulk Notifications
```

### 2. Create Bulk Notification Page

**File**: `app/Filament/Resources/BulkNotifications/Pages/CreateBulkNotification.php`

```php
final class CreateBulkNotification extends CreateRecord
{
    protected static string $resource = BulkNotificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sendToAll = $data['send_to_all'] ?? true;
        $sendType = $data['send_type'] ?? 'immediate';

        unset($data['send_to_all'], $data['send_type']);

        // ✅ Clear target_user_ids if sending to all
        if ($sendToAll) {
            $data['target_user_ids'] = null;
        }

        // ✅ Set appropriate status
        if ($sendType === 'scheduled' && $data['scheduled_at']) {
            $data['status'] = BulkNotificationStatus::SCHEDULED;
        } else {
            $data['status'] = BulkNotificationStatus::DRAFT;
            $data['scheduled_at'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        // ✅ Immediate dispatch or scheduled
        if ($record->status === BulkNotificationStatus::DRAFT) {
            SendBulkNotificationJob::dispatch($record);
        } elseif ($record->status === BulkNotificationStatus::SCHEDULED) {
            SendBulkNotificationJob::dispatch($record)
                ->delay($record->scheduled_at);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
```

### 3. Form Schema

**File**: `app/Filament/Resources/BulkNotifications/Schemas/BulkNotificationForm.php`

```php
Section::make('محتوى الإشعار')
    ->schema([
        TextInput::make('title')
            ->label('العنوان')
            ->required()
            ->maxLength(255),
        Textarea::make('body')
            ->label('الرسالة')
            ->required()
            ->rows(4),
        KeyValue::make('data')
            ->label('بيانات إضافية (اختياري)'),
    ]),

Section::make('الإعدادات')
    ->schema([
        Radio::make('send_type')
            ->label('نوع الإرسال')
            ->options([
                'immediate' => 'إرسال فوري',
                'scheduled' => 'جدولة الإرسال',
            ])
            ->default('immediate')
            ->reactive(),
        DateTimePicker::make('scheduled_at')
            ->label('وقت الإرسال المجدول')
            ->visible(fn ($get) => $get('send_type') === 'scheduled')
            ->minDate(now()),
    ]),

Section::make('المستخدمون المستهدفون')
    ->schema([
        Toggle::make('send_to_all')
            ->label('إرسال لجميع المستخدمين')
            ->default(true),
        CheckboxList::make('target_user_ids')
            ->label('اختر المستخدمين')
            ->visible(fn ($get) => !$get('send_to_all'))
            ->options(
                User::whereNotNull('expo_token')
                    ->pluck('name', 'id')
                    ->toArray()
            )
            ->searchable(),
    ]),
```

**Features:**
- ✅ Arabic UI
- ✅ Immediate or scheduled sending
- ✅ Target all users or specific users
- ✅ Custom data payload (key-value pairs)
- ✅ Dynamic form visibility

### 4. Table Configuration

**File**: `app/Filament/Resources/BulkNotifications/Tables/BulkNotificationsTable.php`

Displays bulk notifications with:
- Status badges (draft, sending, sent, failed)
- Sent/total recipients counts
- Created/sent timestamps
- Scheduled time
- Bulk actions

---

## Testing

### Test Command

**File**: `app/Console/Commands/SendTestExpoNotification.php`

```bash
php artisan expo:send-test
```

**Usage:**
```bash
# Test on central database
php artisan expo:send-test

# Test on specific tenant
php artisan tenants:artisan "expo:send-test" --tenant=restaurant
```

**Command Implementation:**
```php
final class SendTestExpoNotification extends Command
{
    use HasATenantsOption, TenantAwareCommand;

    protected $signature = 'expo:send-test';
    protected $description = 'Send a test Expo notification to all users with an Expo token.';

    public function handle()
    {
        $users = User::whereNotNull('expo_token')->get();

        $this->info("Found {$users->count()} users with Expo tokens.");

        foreach ($users as $user) {
            $this->line("Sending to user: {$user->email}");
            try {
                $user->notify(new TestExpoNotification());
            } catch (Exception $e) {
                $this->error("Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info('Done.');
    }
}
```

### Feature Tests

**File**: `tests/Feature/OrderStatusNotificationTest.php`

```php
it('dispatches notification job when order is created', function () {
    Queue::fake();
    
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    
    Queue::assertPushed(SendOrderStatusNotificationJob::class);
});

it('sends notification only if user has expo token', function () {
    Notification::fake();
    
    $userWithToken = User::factory()->create([
        'expo_token' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
    ]);
    $userWithoutToken = User::factory()->create(['expo_token' => null]);
    
    $order = Order::factory()->create(['user_id' => $userWithToken->id]);
    $job = new SendOrderStatusNotificationJob($order);
    $job->handle();
    
    Notification::assertSentTo($userWithToken, OrderStatusNotification::class);
    
    $order2 = Order::factory()->create(['user_id' => $userWithoutToken->id]);
    $job2 = new SendOrderStatusNotificationJob($order2);
    $job2->handle();
    
    Notification::assertNotSentTo($userWithoutToken, OrderStatusNotification::class);
});

it('creates user notification record when sending expo notification', function () {
    $user = User::factory()->create([
        'expo_token' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
    ]);
    $order = Order::factory()->create(['user_id' => $user->id]);
    
    $notification = new OrderStatusNotification($order);
    $expoMessage = $notification->toExpo($user);
    
    expect(UserNotification::where('user_id', $user->id)->exists())->toBeTrue();
});
```

**Run Tests:**
```bash
php artisan test --filter=OrderStatusNotification
```

---

## Workflow Diagrams

### Order Notification Flow

```
┌─────────────────┐
│   User Places   │
│     Order       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Order Created  │
│  (Eloquent)     │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│  OrderObserver      │
│  ::created()        │
└────────┬────────────┘
         │
         ▼
┌─────────────────────────────┐
│ SendOrderStatusNotifJob     │
│ dispatched to Queue         │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────┐
│  Check if user has  │
│  expo_token?        │
└────────┬────────────┘
         │ YES
         ▼
┌─────────────────────────────┐
│ OrderStatusNotification     │
│ ::toExpo()                  │
└────────┬────────────────────┘
         │
         ├──────────────┐
         │              │
         ▼              ▼
┌─────────────────┐  ┌──────────────────┐
│ Create          │  │ Send to Expo     │
│ UserNotification│  │ Push Service     │
└─────────────────┘  └────────┬─────────┘
                              │
                              ▼
                     ┌──────────────────┐
                     │ Mobile Device    │
                     │ Receives Push    │
                     └──────────────────┘
```

### Bulk Notification Flow

```
┌─────────────────┐
│  Admin Creates  │
│  Bulk Notif     │
│  (Filament)     │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Scheduled?          │
└────────┬────────────┘
         │
    ┌────┴────┐
    │         │
   YES       NO
    │         │
    ▼         ▼
┌────────┐  ┌────────┐
│ Delay  │  │ Immed  │
│ Job    │  │ Dispatch
└───┬────┘  └───┬────┘
    │           │
    └─────┬─────┘
          │
          ▼
┌──────────────────────────┐
│ SendBulkNotificationJob  │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│ Update Status: SENDING   │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│ Get Target Users         │
│ (with expo_token)        │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│ For Each User:           │
│  - Send BulkUserNotif    │
│  - Track Success/Failure │
└──────────┬───────────────┘
           │
           ▼
┌──────────────────────────┐
│ Update Final Status:     │
│  - SENT or FAILED        │
│  - Counts & Metrics      │
└──────────────────────────┘
```

---

## Key Files Reference

### Core System Files

| File | Purpose |
|------|---------|
| `app/Models/User.php` | User model with `expo_token` field and routing |
| `app/Models/UserNotification.php` | Notification history model |
| `app/Models/BulkNotification.php` | Bulk notification campaign model |

### Notifications

| File | Purpose |
|------|---------|
| `app/Notifications/OrderStatusNotification.php` | Order update notifications |
| `app/Notifications/BulkUserNotification.php` | Admin broadcast notifications |
| `app/Notifications/TestExpoNotification.php` | Testing notifications |

### Jobs

| File | Purpose |
|------|---------|
| `app/Jobs/SendOrderStatusNotificationJob.php` | Queued order notification delivery |
| `app/Jobs/SendBulkNotificationJob.php` | Bulk notification delivery with metrics |

### Observers

| File | Purpose |
|------|---------|
| `app/Observers/OrderObserver.php` | Triggers notifications on order events |

### Controllers (Token Registration)

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Registration token capture |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Login token update |
| `app/Http/Controllers/Auth/GoogleAuthController.php` | OAuth token handling |

### Filament Resources

| File | Purpose |
|------|---------|
| `app/Filament/Resources/BulkNotifications/BulkNotificationResource.php` | Main resource |
| `app/Filament/Resources/BulkNotifications/Pages/CreateBulkNotification.php` | Create page with dispatch logic |
| `app/Filament/Resources/BulkNotifications/Schemas/BulkNotificationForm.php` | Form schema |

### Commands

| File | Purpose |
|------|---------|
| `app/Console/Commands/SendTestExpoNotification.php` | Test command |

### Migrations

| File | Purpose |
|------|---------|
| `database/migrations/tenant/2025_12_07_184627_add_expo_token_to_users_table.php` | Add token to users |
| `database/migrations/tenant/2026_02_01_115235_create_user_notifications_table.php` | Notification history |
| `database/migrations/tenant/2026_01_31_171304_create_bulk_notifications_table.php` | Bulk campaigns |

---

## Best Practices

### 1. Token Management

✅ **DO:**
- Always validate tokens using `ExpoPushToken::rule()` or cast
- Update token on every login (users may reinstall app)
- Handle token validation failures gracefully
- Log token update attempts for debugging

❌ **DON'T:**
- Don't store invalid token formats
- Don't block authentication if token is invalid
- Don't assume token stays valid forever

### 2. Notification Delivery

✅ **DO:**
- Always check if user has `expo_token` before sending
- Use queued notifications (`ShouldQueue`)
- Log failures with context
- Store notifications in database for history
- Add retry logic for transient failures

❌ **DON'T:**
- Don't send notifications synchronously
- Don't fail the entire process if one notification fails
- Don't send notifications to users without tokens

### 3. Queue Configuration

**Recommended Queue Setup:**

```php
// config/queue.php
'connections' => [
    'notifications' => [
        'driver' => 'database', // or 'redis'
        'queue' => 'notifications',
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => true,
    ],
],
```

**Worker Command:**
```bash
php artisan queue:work --queue=notifications --tries=3 --timeout=60
```

### 4. Error Handling

```php
try {
    if ($user->expo_token) {
        $user->notify(new OrderStatusNotification($order));
    }
} catch (Exception $exception) {
    // ✅ Log with context
    logger()->error('Failed to send notification', [
        'user_id' => $user->id,
        'order_id' => $order->id,
        'error' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString(),
    ]);
    
    // ✅ Optional: Alert admins for repeated failures
    // ✅ Optional: Mark notification for retry
}
```

### 5. Testing in Development

**Using Expo Go App:**
1. Install Expo Go on your mobile device
2. Get your Expo push token from the app
3. Update a test user's `expo_token` in the database
4. Run: `php artisan expo:send-test`
5. Check your device for the notification

**Using tinker:**
```bash
php artisan tinker
```
```php
$user = User::first();
$user->expo_token = 'ExponentPushToken[your-test-token]';
$user->save();

$user->notify(new \App\Notifications\TestExpoNotification());
```

### 6. Multi-Tenant Considerations

✅ **DO:**
- Run queue workers per tenant if needed
- Use tenant-aware commands (`HasATenantsOption`)
- Ensure notifications respect tenant isolation
- Test notifications in each tenant context

---

## Troubleshooting

### Issue 1: Notifications Not Received

**Symptoms:**
- Job is dispatched
- No errors in logs
- Mobile app doesn't receive notification

**Checklist:**
1. ✅ Verify user has valid `expo_token` in database
2. ✅ Check queue is running: `php artisan queue:work`
3. ✅ Verify token format: `ExponentPushToken[...]`
4. ✅ Check Expo's push notification status page
5. ✅ Ensure mobile app has notification permissions enabled
6. ✅ Verify app is not in development mode (use standalone/production build)

**Debug Commands:**
```bash
# Check user token
php artisan tinker
User::find(1)->expo_token

# Check job status
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue 2: Token Not Saving

**Symptoms:**
- Mobile app sends token
- Token not saved to database

**Debugging:**
```php
// Add logging to authentication controllers
logger()->info('Expo token received', [
    'token' => $request->expo_token,
    'user_id' => $user->id,
]);
```

**Common Causes:**
- Token not included in request
- Token format invalid
- Validation failing silently
- Mass assignment protection

### Issue 3: Queue Jobs Failing

**Symptoms:**
- Jobs in `failed_jobs` table
- Error logs showing failures

**Investigation:**
```bash
# List failed jobs
php artisan queue:failed

# Show specific job details
php artisan queue:failed <job-id>

# Retry specific job
php artisan queue:retry <job-id>
```

**Common Failures:**
- Network timeout connecting to Expo
- Invalid token format
- Missing required fields
- User/Order deleted before job processed

### Issue 4: Notifications Delayed

**Symptoms:**
- Long delay between event and notification

**Causes:**
- Queue worker not running
- Too many jobs in queue
- Worker processing other queues first
- Job delayed intentionally (scheduled)

**Solutions:**
```bash
# Check queue size
php artisan queue:monitor

# Increase worker count
php artisan queue:work --queue=default,notifications

# Use supervisor to manage workers (production)
```

### Issue 5: Arabic Text Not Displaying

**Symptoms:**
- Notifications show garbled text or boxes

**Solution:**
- Ensure mobile app supports Arabic/RTL
- Verify database charset is `utf8mb4`
- Check notification payload encoding

### Issue 6: Duplicate Notifications

**Symptoms:**
- User receives same notification multiple times

**Causes:**
- Observer firing multiple times
- Job dispatched multiple times
- Queue retry mechanism

**Prevention:**
```php
// Add job deduplication
class SendOrderStatusNotificationJob implements ShouldQueue, ShouldBeUnique
{
    public int $uniqueFor = 60; // seconds
    
    public function uniqueId(): string
    {
        return "order-notification-{$this->order->id}";
    }
}
```

---

## Performance Optimization

### 1. Batch Notifications

For bulk notifications, consider batching:

```php
use Illuminate\Support\Facades\Notification;

$users = User::whereNotNull('expo_token')->get();
Notification::send($users, new BulkUserNotification($bulkNotification));
```

### 2. Eager Loading

```php
// Avoid N+1 when sending to multiple users
$orders = Order::with('user')->get();

foreach ($orders as $order) {
    if ($order->user?->expo_token) {
        $order->user->notify(new OrderStatusNotification($order));
    }
}
```

### 3. Queue Configuration

Use Redis for better queue performance:

```bash
composer require predis/predis
```

```env
QUEUE_CONNECTION=redis
```

### 4. Horizon (Optional)

For large-scale notification delivery:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

---

## Security Considerations

### 1. Token Privacy

- ✅ Never expose expo tokens in API responses
- ✅ Don't log tokens in production
- ✅ Restrict token updates to authenticated users only

### 2. Notification Content

- ✅ Sanitize user input in notification content
- ✅ Avoid sending sensitive data in payloads
- ✅ Use notification IDs, not full data objects

### 3. Rate Limiting

Implement rate limiting for bulk notifications:

```php
// In BulkNotificationResource
protected static function canCreate(): bool
{
    // Limit to 10 bulk notifications per hour
    $recentCount = BulkNotification::where('created_at', '>', now()->subHour())
        ->count();
    
    return $recentCount < 10;
}
```

---

## Future Enhancements

### Potential Improvements

1. **Rich Notifications**
   - Add image/media support
   - Action buttons
   - Custom notification layouts

2. **User Preferences**
   - Allow users to opt-out of certain notification types
   - Notification frequency controls
   - Quiet hours settings

3. **Analytics**
   - Track notification open rates
   - Measure engagement
   - A/B testing for notification content

4. **Advanced Scheduling**
   - Recurring notifications
   - User timezone-aware scheduling
   - Smart send time optimization

5. **Localization**
   - Multi-language support based on user preference
   - Dynamic content translation

---

## Conclusion

This Expo Push Notification system provides a robust, scalable solution for engaging mobile app users with real-time updates. The system is:

- ✅ **Reliable**: Queue-based with error handling and retries
- ✅ **Scalable**: Handles bulk notifications efficiently
- ✅ **Maintainable**: Clean architecture with separation of concerns
- ✅ **Testable**: Comprehensive test coverage
- ✅ **Flexible**: Supports various notification types and scheduling
- ✅ **Multi-tenant**: Works seamlessly with tenancy system

For questions or issues, refer to:
- [Expo Push Notifications Documentation](https://docs.expo.dev/push-notifications/overview/)
- [Laravel Expo Channel Package](https://github.com/laravel-notification-channels/expo)
- Project logs: `storage/logs/laravel.log`

---

**Document Version**: 1.0  
**Last Updated**: March 10, 2026  
**Author**: Development Team - Turbo Tenant Project
