<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use \App\Filament\Resources\UserResource\RelationManagers\WorkoutsRelationManager;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getRelationManagers(): array
    {
        return [
            'workouts' => WorkoutsRelationManager::class,
        ];
    }
}
