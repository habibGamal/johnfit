<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\RelationManagers\InBodyLogsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PlanAssignmentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\MealPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assign_workout')
                ->label('Assign Workout Plan')
                ->icon('heroicon-o-fire')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('plan_id')
                        ->label('Select Workout Plan')
                        ->options(WorkoutPlan::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $set('end_date', Carbon::parse($state)->addDays(28)->toDateString());
                            }
                        }),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date (4 Weeks / 28 Days)')
                        ->default(now()->addDays(28)->toDateString())
                        ->disabled()
                        ->dehydrated(),
                ])
                ->action(function (array $data) {
                    /** @var User $user */
                    $user = $this->getRecord();
                    $startDate = Carbon::parse($data['start_date']);
                    $endDate = $startDate->copy()->addDays(28);

                    app(PlanAssignmentService::class)->assignPlan(
                        $user,
                        (int) $data['plan_id'],
                        'workout',
                        $startDate,
                        $endDate
                    );

                    Notification::make()
                        ->title('Workout Plan Assigned')
                        ->body("Successfully assigned workout plan to {$user->name}.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('assign_meal')
                ->label('Assign Meal Plan')
                ->icon('heroicon-o-cake')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('plan_id')
                        ->label('Select Meal Plan')
                        ->options(MealPlan::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $set('end_date', Carbon::parse($state)->addDays(28)->toDateString());
                            }
                        }),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date (4 Weeks / 28 Days)')
                        ->default(now()->addDays(28)->toDateString())
                        ->disabled()
                        ->dehydrated(),
                ])
                ->action(function (array $data) {
                    /** @var User $user */
                    $user = $this->getRecord();
                    $startDate = Carbon::parse($data['start_date']);
                    $endDate = $startDate->copy()->addDays(28);

                    app(PlanAssignmentService::class)->assignPlan(
                        $user,
                        (int) $data['plan_id'],
                        'meal',
                        $startDate,
                        $endDate
                    );

                    Notification::make()
                        ->title('Meal Plan Assigned')
                        ->body("Successfully assigned meal plan to {$user->name}.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('add_subscription')
                ->label('Add Subscription')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('plan_id')
                        ->label('Subscription Plan')
                        ->options(SubscriptionPlan::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if ($state) {
                                $plan = SubscriptionPlan::find($state);
                                $startDate = $get('start_date') ? Carbon::parse($get('start_date')) : now();
                                if ($plan && $plan->duration_days) {
                                    $set('end_date', $startDate->copy()->addDays($plan->duration_days)->toDateTimeString());
                                }
                            }
                        }),
                    Forms\Components\DateTimePicker::make('start_date')
                        ->label('Start Date & Time')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $planId = $get('plan_id');
                            if ($state && $planId) {
                                $plan = SubscriptionPlan::find($planId);
                                if ($plan && $plan->duration_days) {
                                    $set('end_date', Carbon::parse($state)->addDays($plan->duration_days)->toDateTimeString());
                                }
                            }
                        }),
                    Forms\Components\DateTimePicker::make('end_date')
                        ->label('End Date & Time')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'pending' => 'Pending',
                            'expired' => 'Expired',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('active')
                        ->required(),
                ])
                ->action(function (array $data) {
                    /** @var User $user */
                    $user = $this->getRecord();
                    $user->subscriptions()->create($data);

                    Notification::make()
                        ->title('Subscription Added')
                        ->body("Successfully added subscription for {$user->name}.")
                        ->success()
                        ->send();
                }),

            Actions\EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            PlanAssignmentsRelationManager::class,
            SubscriptionsRelationManager::class,
            InBodyLogsRelationManager::class,
        ];
    }
}
