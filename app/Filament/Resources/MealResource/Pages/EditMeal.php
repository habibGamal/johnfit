<?php

namespace App\Filament\Resources\MealResource\Pages;

use App\Filament\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeal extends EditRecord
{
    protected static string $resource = MealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // convert vitamins from ['A' => 100] to ['key' => 'A', 'value' => 100]
        $data['vitamins'] = collect($data['vitamins'])->map(function ($value, $key) {
            return ['key' => $key, 'value' => $value];
        })->values()->toArray();

        return $data;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // convert vitamins from ['key' => 'A', 'value' => '100'] to ['A' => 100]
        $data['vitamins'] = collect($data['vitamins'])->mapWithKeys(function ($vitamin) {
            return [$vitamin['key'] => $vitamin['value']];
        })->toArray();

        return $data;
    }
}
