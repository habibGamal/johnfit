<?php

namespace App\Models;

use App\Observers\SentNotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SentNotificationObserver::class)]
class SentNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'target_type',
        'target_user_ids',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'target_user_ids' => 'array',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
