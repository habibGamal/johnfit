<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $casts = [
        /**
         * {"calories":1.72,"proteins":0.2829,"fats":0.056600000000000004,"carbs":0}
         */
        'meal_macros' => 'array',
        /**
         * {"Ash":1.8,"Iron":1.8,"Zinc":0.3,"Fiber":1.1,"Water":91,"Copper":0,"Niacin":0,"Refuse":0,"Sodium":20,"Betaine":0,"Calcium":213,"Choline":0,"Fluoride":0,"Selenium":0,"Magnesium":0,"Manganese":0,"Potassium":320,"Vitamin A":0,"Vitamin C":35,"Vitamin D":0,"Vitamin E":0,"Vitamin K":0,"Folic Acid":0,"Vitamin B1":0,"Vitamin B2":0,"Vitamin B5":0,"Vitamin B6":0,"Phosphorous":0,"Vitamin B12":0}
         */
        'vitamins' => 'array',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = [
        'calories',
        'protein',
        'carbs',
        'fat',
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

    /**
     * Get the calories value.
     *
     * @return float
     */
    public function getCaloriesAttribute()
    {
        return $this->meal_macros['calories'] ?? 0;
    }

    /**
     * Get the protein value.
     *
     * @return float
     */
    public function getProteinAttribute()
    {
        return $this->meal_macros['proteins'] ?? 0;
    }

    /**
     * Get the carbs value.
     *
     * @return float
     */
    public function getCarbsAttribute()
    {
        return $this->meal_macros['carbs'] ?? 0;
    }

    /**
     * Get the fat value.
     *
     * @return float
     */
    public function getFatAttribute()
    {
        return $this->meal_macros['fats'] ?? 0;
    }
}
