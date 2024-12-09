<?php

namespace App\Filament\Resources\RepsPresetResource\Pages;

use App\Filament\Resources\RepsPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepsPresets extends ListRecords
{
    protected static string $resource = RepsPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
