<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $casts = [
        'meal_macros' => 'array',
        'vitamins' => 'array',
    ];

    /**
     * Get the meal type as an array.
     *
     * @param string $value
     * @return array
     */
    public function getTypeAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * Set the meal type from an array.
     *
     * @param array $value
     * @return void
     */
    public function setTypeAttribute(array $value)
    {
        $this->attributes['type'] = implode(',', $value);
    }
}
