<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepsPreset extends Model
{
    protected $casts = [
        'reps' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($repsPreset) {
            if (empty($repsPreset->short_name)) {
                $repsPreset->short_name = implode(
                    ',',
                    array_map(function ($reps) {
                        return $reps['count'];
                    }, $repsPreset->reps)
                );
            }
        });
    }

    public function getValueAttribute()
    {
        return implode(
            ',',
            array_map(function ($reps) {
                return $reps['count'];
            }, $this->reps)
        );
    }
}
