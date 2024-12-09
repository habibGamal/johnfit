<?php

namespace App\Filament\Resources\MealResource\Pages;

use App\Filament\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // convert vitamins from ['key' => 'A', 'value' => '100'] to ['A' => 100]
        $data['vitamins'] = collect($data['vitamins'])->mapWithKeys(function ($vitamin) {
            return [$vitamin['key'] => $vitamin['value']];
        })->toArray();

        return $data;
    }
}
