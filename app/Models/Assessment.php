<?php

namespace App\Models;

use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'image',
        'type',
        'options',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssessmentType::class,
            'options' => 'array',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAssessmentAnswer::class);
    }
}
