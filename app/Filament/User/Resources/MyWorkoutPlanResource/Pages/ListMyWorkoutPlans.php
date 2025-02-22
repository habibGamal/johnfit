<?php

namespace App\Filament\User\Resources\MyWorkoutPlanResource\Pages;

use App\Filament\User\Resources\MyWorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyWorkoutPlans extends ListRecords
{
    protected static string $resource = MyWorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
