<?php

namespace App\Filament\Resources\WorkoutPlanResource\Pages;

use App\Filament\Resources\WorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutPlans extends ListRecords
{
    protected static string $resource = WorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
