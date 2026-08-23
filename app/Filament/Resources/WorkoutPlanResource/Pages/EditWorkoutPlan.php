<?php

namespace App\Filament\Resources\WorkoutPlanResource\Pages;

use App\Filament\Resources\WorkoutPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditWorkoutPlan extends EditRecord
{
    protected static string $resource = WorkoutPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $plan_file = Storage::disk('local')->get($data['file_path']);
        $days = json_decode($plan_file, true) ?? [];

        // Normalize days: ensure every workout slot has an options repeater array
        $normalizedDays = collect($days)->map(function ($day) {
            $day['workouts'] = collect($day['workouts'] ?? [])->map(function ($slot) {
                if (isset($slot['options']) && is_array($slot['options'])) {
                    return $slot;
                }
                return [
                    'options' => [
                        [
                            'workout_id' => $slot['workout_id'] ?? null,
                            'reps' => $slot['reps'] ?? null,
                        ],
                    ],
                ];
            })->all();

            return $day;
        })->all();

        $data['days'] = $normalizedDays;
        return $data;
    }

    protected function afterSave(): void
    {
        $strategy = $this->form->getRawState()['update_strategy'] ?? $this->data['update_strategy'] ?? 'future_only';
        if ($strategy !== 'none') {
            $count = app(\App\Services\DailyScheduleService::class)->syncPlanUpdateToAssignedUsers(
                $this->getRecord()->id,
                'workout',
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
