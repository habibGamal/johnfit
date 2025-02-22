<?php

namespace App\Filament\User\Resources\MyWorkoutPlanResource\Pages;

use App\Filament\User\Resources\MyWorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyWorkoutPlan extends EditRecord
{
    protected static string $resource = MyWorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
