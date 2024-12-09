<?php

namespace App\Filament\Resources\RepsPresetResource\Pages;

use App\Filament\Resources\RepsPresetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepsPreset extends EditRecord
{
    protected static string $resource = RepsPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
