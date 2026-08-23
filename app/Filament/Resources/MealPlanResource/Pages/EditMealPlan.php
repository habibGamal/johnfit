<?php

namespace App\Filament\Resources\MealPlanResource\Pages;

use App\Filament\Resources\MealPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditMealPlan extends EditRecord
{
    protected static string $resource = MealPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $plan_file = Storage::disk('local')->get($data['file_path']);
        $data['days'] = json_decode($plan_file, true);
        return $data;
    }

    protected function afterSave(): void
    {
        $strategy = $this->form->getRawState()['update_strategy'] ?? $this->data['update_strategy'] ?? 'future_only';
        if ($strategy !== 'none') {
            $count = app(\App\Services\DailyScheduleService::class)->syncPlanUpdateToAssignedUsers(
                $this->getRecord()->id,
                'meal',
                $strategy
            );

            if ($count > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('Schedules Synced')
                    ->body("Updated active schedules for {$count} assigned user(s) using '{$strategy}' strategy.")
                    ->success()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
