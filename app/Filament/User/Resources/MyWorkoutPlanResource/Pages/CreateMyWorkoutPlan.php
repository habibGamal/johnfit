<?php

namespace App\Filament\User\Resources\MyWorkoutPlanResource\Pages;

use App\Filament\User\Resources\MyWorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMyWorkoutPlan extends CreateRecord
{
    protected static string $resource = MyWorkoutPlanResource::class;
}
