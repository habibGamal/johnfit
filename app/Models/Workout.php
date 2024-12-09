<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    static $predefined_tools =  [
        "Dumbbell"          => "Dumbbell",
        "Bodyweight"        => "Bodyweight",
        "Machine"           => "Machine",
        "Cable"             => "Cable",
        "Dumbbells"         => "Dumbbells",
        "Machines"          => "Machines",
        "Barbell"           => "Barbell",
        "Band"              => "Band",
        "Broomstick"        => "Broomstick",
        "Resistance Band"   => "Resistance Band",
        "Ez-Bar"            => "Ez-Bar",
        "Jump Rope"         => "Jump Rope",
        "Kettlebell"        => "Kettlebell",
        "Medicine Ball"     => "Medicine Ball",
        "Plates"            => "Plates",
        "Trx"               => "Trx",
        "Stability Ball"    => "Stability Ball",
        "Balance Ball"      => "Balance Ball",
        "Tire"              => "Tire",
    ];

    static $predefined_muscels = [
        "Shoulder" => "Shoulder",
        "Chest" => "Chest",
        "Glutes" => "Glutes",
        "Abdominals" => "Abdominals",
        "Triceps" => "Triceps",
        "Lat" => "Lat",
        "Lower Back" => "Lower Back",
        "Hip Adductors" => "Hip Adductors",
        "Hip Abductors" => "Hip Abductors",
        "Quadriceps" => "Quadriceps",
        "Stretching" => "Stretching",
        "Obliques" => "Obliques",
        "Core" => "Core",
        "Calf" => "Calf",
        "Lats" => "Lats",
        "Forearm" => "Forearm",
        "All Posterior Chain Muscles" => "All Posterior Chain Muscles",
        "Shrug" => "Shrug",
        "Biceps" => "Biceps",
        "Shoulders" => "Shoulders",
        "Trapezius" => "Trapezius",
        "Hip Mobility" => "Hip Mobility",
        "Hamstrings" => "Hamstrings",
        "Gluteus Maximus" => "Gluteus Maximus",
        "Adductors" => "Adductors",
        "Deltoid" => "Deltoid",
        "Abductors" => "Abductors",
        "Compound Exercise" => "Compound Exercise",
        "Rotator Cuff" => "Rotator Cuff",
        "Anterior Deltoid" => "Anterior Deltoid",
    ];

    /**
     * Get the muscles as an array.
     *
     * @param string $value
     * @return array
     */
    public function getMusclesAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * Set the muscles from an array.
     *
     * @param array $value
     * @return void
     */
    public function setMusclesAttribute(array $value)
    {
        $this->attributes['muscles'] = implode(',', $value);
    }

    /**
     * Get the tools as an array.
     *
     * @param string $value
     * @return array
     */
    public function getToolsAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * Set the tools from an array.
     *
     * @param array $value
     * @return void
     */
    public function setToolsAttribute(array $value)
    {
        $this->attributes['tools'] = implode(',', $value);
    }
}
