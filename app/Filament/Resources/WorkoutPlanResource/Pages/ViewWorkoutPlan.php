<?php

namespace App\Filament\Resources\WorkoutPlanResource\Pages;

use App\Filament\Resources\WorkoutPlanResource;
use App\Filament\Resources\WorkoutPlanResource\RelationManagers\UsersRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkoutPlan extends ViewRecord
{
    protected static string $resource = WorkoutPlanResource::class;


    public function getRelationManagers(): array
    {
        return [
            'users' => UsersRelationManager::class,
        ];
    }
}
