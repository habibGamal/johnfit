<?php

namespace App\Filament\User\Resources\MyWorkoutPlanResource\Pages;

use App\Filament\User\Resources\MyWorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMyWorkoutPlan extends ViewRecord
{
    protected static string $resource = MyWorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
