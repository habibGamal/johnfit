<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InBodyLog extends Model
{
    /** @use HasFactory<\Database\Factories\InBodyLogFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inbody_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'weight',
        'smm',
        'pbf',
        'bmi',
        'bmr',
        'body_water',
        'lean_body_mass',
        'visceral_fat',
        'waist_hip_ratio',
        'tenant_id',
        'measured_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'smm' => 'decimal:2',
            'pbf' => 'decimal:2',
            'bmi' => 'decimal:1',
            'bmr' => 'decimal:1',
            'body_water' => 'decimal:2',
            'lean_body_mass' => 'decimal:2',
            'visceral_fat' => 'decimal:1',
            'waist_hip_ratio' => 'decimal:3',
            'measured_at' => 'date',
        ];
    }

    /**
     * Get the user that owns this InBody log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant($query, ?int $tenantId)
    {
        if ($tenantId !== null) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    /**
     * Scope to get logs in chronological order.
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('measured_at', 'asc');
    }

    /**
     * Scope to get latest logs first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('measured_at', 'desc');
    }

    /**
     * Get the previous log entry for this user.
     */
    public function getPreviousLog(): ?self
    {
        return static::where('user_id', $this->user_id)
            ->where('measured_at', '<', $this->measured_at)
            ->latestFirst()
            ->first();
    }
}
