<?php

namespace App\Filament\Resources\RepsPresetResource\Pages;

use App\Filament\Resources\RepsPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRepsPreset extends CreateRecord
{
    protected static string $resource = RepsPresetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['short_name'] = implode(
            ',',
            array_map(function ($reps) {
                return $reps['count'];
            }, $data['reps'])
        );

        return $data;
    }
}
