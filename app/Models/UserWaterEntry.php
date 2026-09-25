<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWaterEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'water_log_id',
        'amount_ml',
        'container_type',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_ml' => 'integer',
            'logged_at' => 'datetime',
        ];
    }

    public function waterLog(): BelongsTo
    {
        return $this->belongsTo(UserDailyWaterLog::class, 'water_log_id');
    }
}
