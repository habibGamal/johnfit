<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\RelationManagers\InBodyLogsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WorkoutsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getRelationManagers(): array
    {
        return [
            'workouts' => WorkoutsRelationManager::class,
            'inBodyLogs' => InBodyLogsRelationManager::class,
        ];
    }
}
