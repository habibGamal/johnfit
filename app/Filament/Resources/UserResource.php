<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\MealPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\DailyScheduleService;
use App\Services\PlanAssignmentService;
use App\Services\WaterIntakeService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state)),
                Forms\Components\TextInput::make('role')
                    ->required()
                    ->maxLength(255)
                    ->default('user'),

                Forms\Components\Section::make('Hydration & Water Settings')
                    ->description('Configure client daily water target strategy, coach custom targets, and hydration notes.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('water_target_mode')
                            ->label('Target Calculation Mode')
                            ->options([
                                'auto' => 'Auto (Tier 1 Base Weight / Tier 2 InBody + Workout Bonus)',
                                'fixed' => 'Fixed Coach Target (ml)',
                                'custom_multiplier' => 'Custom Ratio (ml per kg)',
                            ])
                            ->default('auto')
                            ->live(),
                        Forms\Components\TextInput::make('admin_water_target_ml')
                            ->label('Fixed Daily Water Target (ml)')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(8000)
                            ->placeholder('e.g. 3500')
                            ->visible(fn (Forms\Get $get) => $get('water_target_mode') === 'fixed')
                            ->required(fn (Forms\Get $get) => $get('water_target_mode') === 'fixed'),
                        Forms\Components\TextInput::make('water_multiplier_per_kg')
                            ->label('Water Multiplier (ml / kg)')
                            ->numeric()
                            ->minValue(25)
                            ->maxValue(60)
                            ->placeholder('e.g. 40.0')
                            ->visible(fn (Forms\Get $get) => $get('water_target_mode') === 'custom_multiplier')
                            ->required(fn (Forms\Get $get) => $get('water_target_mode') === 'custom_multiplier'),
                        Forms\Components\Textarea::make('admin_water_notes')
                            ->label('Coach Hydration Notes')
                            ->placeholder('Instructions shown to client on their dashboard (e.g. drink 1L before noon, extra around workout).')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('allow_user_water_override')
                            ->label('Allow User Custom Override')
                            ->helperText('If enabled, the client can customize their personal target in the app.')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // 1. Subscription Status & Details Section
                Section::make('Subscription Status & Membership')
                    ->icon('heroicon-o-credit-card')
                    ->collapsible()
                    ->columns(4)
                    ->headerActions([
                        InfolistAction::make('add_subscription')
                            ->label('Add Subscription')
                            ->icon('heroicon-o-plus-circle')
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
                            ->action(function (User $record, array $data) {
                                $record->subscriptions()->create($data);

                                Notification::make()
                                    ->title('Subscription Added')
                                    ->body("Added subscription for {$record->name}.")
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        TextEntry::make('subscription_status_display')
                            ->label('Current Status')
                            ->state(function (User $record): string {
                                $sub = $record->activeSubscription();
                                if ($sub) {
                                    return 'Active Subscription';
                                }
                                $latest = $record->latestSubscription();
                                if ($latest) {
                                    return 'Expired (' . ucfirst($latest->status) . ')';
                                }
                                return 'No Subscription';
                            })
                            ->badge()
                            ->color(function (User $record): string {
                                if ($record->hasActiveSubscription()) {
                                    return 'success';
                                }
                                if ($record->latestSubscription()) {
                                    return 'danger';
                                }
                                return 'gray';
                            }),
                        TextEntry::make('subscription_plan_display')
                            ->label('Plan Name')
                            ->state(function (User $record): string {
                                $sub = $record->activeSubscription() ?? $record->latestSubscription();
                                return $sub?->plan?->name ?? 'None';
                            })
                            ->weight('semibold'),
                        TextEntry::make('subscription_validity')
                            ->label('Valid Period')
                            ->state(function (User $record): string {
                                $sub = $record->activeSubscription() ?? $record->latestSubscription();
                                if (! $sub) {
                                    return '—';
                                }
                                $start = $sub->start_date ? $sub->start_date->format('M d, Y') : 'N/A';
                                $end = $sub->end_date ? $sub->end_date->format('M d, Y') : 'Lifetime';
                                return "{$start} — {$end}";
                            }),
                        TextEntry::make('subscription_time_remaining')
                            ->label('Remaining / Expired')
                            ->state(function (User $record): string {
                                $sub = $record->activeSubscription();
                                if ($sub) {
                                    if (! $sub->end_date) {
                                        return 'Lifetime Access';
                                    }
                                    return $sub->end_date->diffForHumans(now(), ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]);
                                }
                                $latest = $record->latestSubscription();
                                if ($latest && $latest->end_date) {
                                    return 'Ended ' . $latest->end_date->diffForHumans();
                                }
                                return 'No Subscription History';
                            })
                            ->badge()
                            ->color(function (User $record): string {
                                $sub = $record->activeSubscription();
                                if (! $sub) {
                                    return 'gray';
                                }
                                if ($sub->end_date && $sub->end_date->diffInDays(now()) <= 3) {
                                    return 'warning';
                                }
                                return 'success';
                            }),
                    ]),

                // 2. Active Workout Plan Central Control Section
                Section::make('Workout Plan Central Control')
                    ->icon('heroicon-o-fire')
                    ->description('Active workout assignment, cycle progress, and schedule materialization')
                    ->columns(4)
                    ->headerActions([
                        InfolistAction::make('assign_workout_plan')
                            ->label('Assign / Switch Workout')
                            ->icon('heroicon-o-plus-circle')
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
                            ->action(function (User $record, array $data) {
                                $startDate = Carbon::parse($data['start_date']);
                                $endDate = $startDate->copy()->addDays(28);

                                app(PlanAssignmentService::class)->assignPlan(
                                    $record,
                                    (int) $data['plan_id'],
                                    'workout',
                                    $startDate,
                                    $endDate
                                );

                                Notification::make()
                                    ->title('Workout Plan Assigned')
                                    ->body("Successfully assigned workout plan to {$record->name}.")
                                    ->success()
                                    ->send();
                            }),
                        InfolistAction::make('extend_workout_plan')
                            ->label('Extend (+28 Days)')
                            ->icon('heroicon-o-arrow-path')
                            ->color('success')
                            ->visible(fn (User $record) => $record->activeWorkoutPlanAssignment() !== null)
                            ->requiresConfirmation()
                            ->modalHeading('Extend Workout Plan Assignment')
                            ->modalDescription('This will extend the current workout plan by 28 days and materialize additional daily schedules.')
                            ->action(function (User $record) {
                                $activeAssignment = $record->activeWorkoutPlanAssignment();
                                if (! $activeAssignment) {
                                    return;
                                }

                                $newEndDate = $activeAssignment->end_date
                                    ? Carbon::parse($activeAssignment->end_date)->addDays(28)
                                    : now()->addDays(28);

                                $activeAssignment->update(['end_date' => $newEndDate->toDateString()]);

                                $service = app(DailyScheduleService::class);
                                $period = CarbonPeriod::create(now()->toDateString(), now()->addDays(7)->toDateString());
                                foreach ($period as $date) {
                                    $service->materializeDateForUser($record, $date);
                                }

                                Notification::make()
                                    ->title('Workout Plan Extended')
                                    ->body("Extended {$activeAssignment->plan_name} to {$newEndDate->format('M d, Y')}.")
                                    ->success()
                                    ->send();
                            }),
                        InfolistAction::make('cancel_workout_plan')
                            ->label('Deactivate')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->visible(fn (User $record) => $record->activeWorkoutPlanAssignment() !== null)
                            ->requiresConfirmation()
                            ->modalHeading('Deactivate Workout Plan')
                            ->modalDescription('Are you sure you want to deactivate the active workout plan for this user?')
                            ->action(function (User $record) {
                                $assignment = $record->activeWorkoutPlanAssignment();
                                if ($assignment) {
                                    $assignment->update(['status' => 'cancelled']);
                                    Notification::make()
                                        ->title('Workout Plan Deactivated')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ])
                    ->schema([
                        TextEntry::make('active_workout_name')
                            ->label('Current Plan')
                            ->state(fn (User $record): string => $record->activeWorkoutPlanAssignment()?->plan_name ?? 'No active workout plan')
                            ->weight('bold')
                            ->color(fn (User $record): string => $record->activeWorkoutPlanAssignment() ? 'primary' : 'gray'),
                        TextEntry::make('active_workout_dates')
                            ->label('Date Window')
                            ->state(function (User $record): string {
                                $assignment = $record->activeWorkoutPlanAssignment();
                                if (! $assignment) {
                                    return '—';
                                }
                                $start = $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A';
                                $end = $assignment->end_date ? $assignment->end_date->format('M d, Y') : 'Ongoing';
                                return "{$start} — {$end}";
                            }),
                        TextEntry::make('active_workout_status')
                            ->label('Status & Deadline')
                            ->state(function (User $record): string {
                                $assignment = $record->activeWorkoutPlanAssignment();
                                if (! $assignment) {
                                    return 'Needs Assignment';
                                }
                                if (! $assignment->end_date) {
                                    return 'Active (Ongoing)';
                                }
                                $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                                if ($diff < 0) {
                                    return 'Finished (' . abs($diff) . ' days ago)';
                                }
                                if ($diff <= 3) {
                                    return "Expiring Soon ({$diff} " . ($diff === 1 ? 'day' : 'days') . ' left)';
                                }
                                return "Active ({$diff} days left)";
                            })
                            ->badge()
                            ->color(function (User $record): string {
                                $assignment = $record->activeWorkoutPlanAssignment();
                                if (! $assignment) {
                                    return $record->hasActiveSubscription() ? 'danger' : 'gray';
                                }
                                if (! $assignment->end_date) {
                                    return 'success';
                                }
                                $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                                if ($diff < 0) {
                                    return 'danger';
                                }
                                if ($diff <= 3) {
                                    return 'warning';
                                }
                                return 'success';
                            }),
                        TextEntry::make('active_workout_progress')
                            ->label('Cycle Progress')
                            ->state(function (User $record): string {
                                $assignment = $record->activeWorkoutPlanAssignment();
                                if (! $assignment || ! $assignment->start_date || ! $assignment->end_date) {
                                    return '—';
                                }
                                $start = Carbon::parse($assignment->start_date)->startOfDay();
                                $end = Carbon::parse($assignment->end_date)->startOfDay();
                                $totalDays = $start->diffInDays($end) ?: 1;
                                $elapsed = max(0, $start->diffInDays(now()->startOfDay(), false));
                                $dayNum = min($totalDays, $elapsed + 1);
                                return "Day {$dayNum} of {$totalDays}";
                            }),
                    ]),

                // 3. Active Meal Plan Central Control Section
                Section::make('Meal Plan Central Control')
                    ->icon('heroicon-o-cake')
                    ->description('Active nutritional plan assignment, calorie strategy, and meal schedules')
                    ->columns(4)
                    ->headerActions([
                        InfolistAction::make('assign_meal_plan')
                            ->label('Assign / Switch Meal')
                            ->icon('heroicon-o-plus-circle')
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
                            ->action(function (User $record, array $data) {
                                $startDate = Carbon::parse($data['start_date']);
                                $endDate = $startDate->copy()->addDays(28);

                                app(PlanAssignmentService::class)->assignPlan(
                                    $record,
                                    (int) $data['plan_id'],
                                    'meal',
                                    $startDate,
                                    $endDate
                                );

                                Notification::make()
                                    ->title('Meal Plan Assigned')
                                    ->body("Successfully assigned meal plan to {$record->name}.")
                                    ->success()
                                    ->send();
                            }),
                        InfolistAction::make('extend_meal_plan')
                            ->label('Extend (+28 Days)')
                            ->icon('heroicon-o-arrow-path')
                            ->color('success')
                            ->visible(fn (User $record) => $record->activeMealPlanAssignment() !== null)
                            ->requiresConfirmation()
                            ->modalHeading('Extend Meal Plan Assignment')
                            ->modalDescription('This will extend the current meal plan by 28 days and materialize additional daily schedules.')
                            ->action(function (User $record) {
                                $activeAssignment = $record->activeMealPlanAssignment();
                                if (! $activeAssignment) {
                                    return;
                                }

                                $newEndDate = $activeAssignment->end_date
                                    ? Carbon::parse($activeAssignment->end_date)->addDays(28)
                                    : now()->addDays(28);

                                $activeAssignment->update(['end_date' => $newEndDate->toDateString()]);

                                $service = app(DailyScheduleService::class);
                                $period = CarbonPeriod::create(now()->toDateString(), now()->addDays(7)->toDateString());
                                foreach ($period as $date) {
                                    $service->materializeDateForUser($record, $date);
                                }

                                Notification::make()
                                    ->title('Meal Plan Extended')
                                    ->body("Extended {$activeAssignment->plan_name} to {$newEndDate->format('M d, Y')}.")
                                    ->success()
                                    ->send();
                            }),
                        InfolistAction::make('cancel_meal_plan')
                            ->label('Deactivate')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->visible(fn (User $record) => $record->activeMealPlanAssignment() !== null)
                            ->requiresConfirmation()
                            ->modalHeading('Deactivate Meal Plan')
                            ->modalDescription('Are you sure you want to deactivate the active meal plan for this user?')
                            ->action(function (User $record) {
                                $assignment = $record->activeMealPlanAssignment();
                                if ($assignment) {
                                    $assignment->update(['status' => 'cancelled']);
                                    Notification::make()
                                        ->title('Meal Plan Deactivated')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ])
                    ->schema([
                        TextEntry::make('active_meal_name')
                            ->label('Current Plan')
                            ->state(fn (User $record): string => $record->activeMealPlanAssignment()?->plan_name ?? 'No active meal plan')
                            ->weight('bold')
                            ->color(fn (User $record): string => $record->activeMealPlanAssignment() ? 'success' : 'gray'),
                        TextEntry::make('active_meal_dates')
                            ->label('Date Window')
                            ->state(function (User $record): string {
                                $assignment = $record->activeMealPlanAssignment();
                                if (! $assignment) {
                                    return '—';
                                }
                                $start = $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A';
                                $end = $assignment->end_date ? $assignment->end_date->format('M d, Y') : 'Ongoing';
                                return "{$start} — {$end}";
                            }),
                        TextEntry::make('active_meal_status')
                            ->label('Status & Deadline')
                            ->state(function (User $record): string {
                                $assignment = $record->activeMealPlanAssignment();
                                if (! $assignment) {
                                    return 'Needs Assignment';
                                }
                                if (! $assignment->end_date) {
                                    return 'Active (Ongoing)';
                                }
                                $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                                if ($diff < 0) {
                                    return 'Finished (' . abs($diff) . ' days ago)';
                                }
                                if ($diff <= 3) {
                                    return "Expiring Soon ({$diff} " . ($diff === 1 ? 'day' : 'days') . ' left)';
                                }
                                return "Active ({$diff} days left)";
                            })
                            ->badge()
                            ->color(function (User $record): string {
                                $assignment = $record->activeMealPlanAssignment();
                                if (! $assignment) {
                                    return $record->hasActiveSubscription() ? 'danger' : 'gray';
                                }
                                if (! $assignment->end_date) {
                                    return 'success';
                                }
                                $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                                if ($diff < 0) {
                                    return 'danger';
                                }
                                if ($diff <= 3) {
                                    return 'warning';
                                }
                                return 'success';
                            }),
                        TextEntry::make('active_meal_progress')
                            ->label('Cycle Progress')
                            ->state(function (User $record): string {
                                $assignment = $record->activeMealPlanAssignment();
                                if (! $assignment || ! $assignment->start_date || ! $assignment->end_date) {
                                    return '—';
                                }
                                $start = Carbon::parse($assignment->start_date)->startOfDay();
                                $end = Carbon::parse($assignment->end_date)->startOfDay();
                                $totalDays = $start->diffInDays($end) ?: 1;
                                $elapsed = max(0, $start->diffInDays(now()->startOfDay(), false));
                                $dayNum = min($totalDays, $elapsed + 1);
                                return "Day {$dayNum} of {$totalDays}";
                            }),
                    ]),

                // 4. User Details Section
                Section::make('User Details')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'admin' ? 'danger' : 'primary'),
                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not verified'),
                        TextEntry::make('assessment_completed_at')
                            ->label('Assessment Completed')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not completed'),
                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->dateTime('M d, Y'),
                    ]),

                // 5. Assessment Answers Section
                Section::make('Assessment Answers')
                    ->description('Answers submitted by the user during the initial assessment.')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('assessmentAnswers')
                            ->label('')
                            ->schema([
                                TextEntry::make('assessment.question')
                                    ->label('Question')
                                    ->weight('semibold'),
                                TextEntry::make('answer')
                                    ->label('Answer')
                                    ->formatStateUsing(
                                        fn (mixed $state): string => is_array($state)
                                            ? implode(', ', $state)
                                            : (string) $state
                                    ),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->visible(fn (User $record): bool => $record->hasCompletedAssessment()),

                // 6. Hydration & Water Intake Section
                Section::make('Hydration & Daily Water Target')
                    ->icon('heroicon-o-beaker')
                    ->collapsible()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('water_target_mode')
                            ->label('Target Mode')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'fixed' => 'warning',
                                'custom_multiplier' => 'info',
                                default => 'success',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'fixed' => 'Fixed Coach Target',
                                'custom_multiplier' => 'Custom Ratio',
                                default => 'Auto (Tier 1 / 2)',
                            }),
                        TextEntry::make('daily_water_target')
                            ->label('Effective Daily Target')
                            ->state(function (User $record): string {
                                $data = app(WaterIntakeService::class)->calculateDailyTarget($record);
                                return number_format($data['target_ml']) . ' ml (' . $data['tier_name'] . ')';
                            })
                            ->weight('bold'),
                        TextEntry::make('today_water_intake')
                            ->label("Today's Consumed")
                            ->state(function (User $record): string {
                                $log = $record->todayWaterLog;
                                if (! $log) {
                                    return '0 ml (0%)';
                                }
                                return number_format($log->consumed_ml) . ' ml (' . $log->percentage . '%)';
                            })
                            ->badge()
                            ->color(function (User $record): string {
                                $log = $record->todayWaterLog;
                                return ($log && $log->is_completed) ? 'success' : 'gray';
                            }),
                        TextEntry::make('allow_user_water_override')
                            ->label('User Override')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Allowed' : 'Locked by Coach'),
                        TextEntry::make('admin_water_notes')
                            ->label('Coach Hydration Instructions')
                            ->placeholder('No specific instructions set')
                            ->columnSpanFull(),
                    ]),

                // 7. Achievements & Streaks Section
                Section::make('Achievements & Streaks')
                    ->icon('heroicon-o-trophy')
                    ->collapsible()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('badges_unlocked')
                            ->label('Badges Unlocked')
                            ->state(function (User $record): string {
                                $unlocked = $record->userBadges()->count();
                                $total = \App\Models\Badge::where('is_active', true)->count();

                                return $unlocked . ' / ' . $total;
                            })
                            ->badge()
                            ->color(fn (User $record): string => $record->userBadges()->count() > 0 ? 'success' : 'gray'),

                        TextEntry::make('streak_workout')
                            ->label('Workout Streak')
                            ->state(fn (User $record): string => $this->streakText($record, 'workout'))
                            ->badge()
                            ->color(fn (User $record): string => ($record->streaks->firstWhere('streak_type', 'workout')?->current_streak ?? 0) > 0 ? 'warning' : 'gray'),

                        TextEntry::make('streak_meal')
                            ->label('Meal Streak')
                            ->state(fn (User $record): string => $this->streakText($record, 'meal'))
                            ->badge()
                            ->color(fn (User $record): string => ($record->streaks->firstWhere('streak_type', 'meal')?->current_streak ?? 0) > 0 ? 'warning' : 'gray'),

                        TextEntry::make('streak_hydration')
                            ->label('Hydration Streak')
                            ->state(fn (User $record): string => $this->streakText($record, 'hydration'))
                            ->badge()
                            ->color(fn (User $record): string => ($record->streaks->firstWhere('streak_type', 'hydration')?->current_streak ?? 0) > 0 ? 'warning' : 'gray'),

                        TextEntry::make('streak_overall')
                            ->label('Perfect Day Streak')
                            ->state(fn (User $record): string => $this->streakText($record, 'overall'))
                            ->badge()
                            ->color(fn (User $record): string => ($record->streaks->firstWhere('streak_type', 'overall')?->current_streak ?? 0) > 0 ? 'warning' : 'gray'),

                        TextEntry::make('earned_badges_list')
                            ->label('Badges Earned')
                            ->state(function (User $record): string {
                                $names = $record->userBadges()
                                    ->with('badge')
                                    ->get()
                                    ->map(fn ($userBadge) => $userBadge->badge?->name)
                                    ->filter()
                                    ->implode(', ');

                                return $names !== '' ? $names : 'None yet';
                            })
                            ->placeholder('None yet')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Format a user's streak as "current (best ever)".
     */
    protected static function streakText(User $record, string $type): string
    {
        $streak = $record->streaks->firstWhere('streak_type', $type);

        if (! $streak) {
            return '—';
        }

        return $streak->current_streak . ' (' . $streak->longest_streak . ')';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'subscriptions.plan',
                'planAssignments.workoutPlan',
                'planAssignments.mealPlan',
                'streaks',
                'todayWaterLog',
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->label('Subscription')
                    ->state(function (User $record): string {
                        $activeSub = $record->activeSubscription();
                        if ($activeSub) {
                            return $activeSub->plan?->name ?? 'Active';
                        }
                        $latest = $record->latestSubscription();
                        if ($latest) {
                            return 'Expired';
                        }
                        return 'None';
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        if ($record->hasActiveSubscription()) {
                            return 'success';
                        }
                        if ($record->latestSubscription()) {
                            return 'danger';
                        }
                        return 'gray';
                    }),
                Tables\Columns\TextColumn::make('active_workout')
                    ->label('Workout Plan')
                    ->state(function (User $record): string {
                        $assignment = $record->activeWorkoutPlanAssignment();
                        if (! $assignment) {
                            return 'None';
                        }
                        if (! $assignment->end_date) {
                            return $assignment->plan_name;
                        }
                        $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                        if ($diff < 0) {
                            return "{$assignment->plan_name} (Ended)";
                        }
                        if ($diff <= 3) {
                            return "{$assignment->plan_name} ({$diff}d left)";
                        }
                        return "{$assignment->plan_name} ({$diff}d)";
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $assignment = $record->activeWorkoutPlanAssignment();
                        if (! $assignment) {
                            return $record->hasActiveSubscription() ? 'danger' : 'gray';
                        }
                        if (! $assignment->end_date) {
                            return 'primary';
                        }
                        $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                        if ($diff < 0) {
                            return 'danger';
                        }
                        if ($diff <= 3) {
                            return 'warning';
                        }
                        return 'primary';
                    }),
                Tables\Columns\TextColumn::make('active_meal')
                    ->label('Meal Plan')
                    ->state(function (User $record): string {
                        $assignment = $record->activeMealPlanAssignment();
                        if (! $assignment) {
                            return 'None';
                        }
                        if (! $assignment->end_date) {
                            return $assignment->plan_name;
                        }
                        $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                        if ($diff < 0) {
                            return "{$assignment->plan_name} (Ended)";
                        }
                        if ($diff <= 3) {
                            return "{$assignment->plan_name} ({$diff}d left)";
                        }
                        return "{$assignment->plan_name} ({$diff}d)";
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $assignment = $record->activeMealPlanAssignment();
                        if (! $assignment) {
                            return $record->hasActiveSubscription() ? 'danger' : 'gray';
                        }
                        if (! $assignment->end_date) {
                            return 'success';
                        }
                        $diff = now()->startOfDay()->diffInDays(Carbon::parse($assignment->end_date)->startOfDay(), false);
                        if ($diff < 0) {
                            return 'danger';
                        }
                        if ($diff <= 3) {
                            return 'warning';
                        }
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'admin' ? 'danger' : 'primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription')
                    ->label('Subscription Status')
                    ->options([
                        'active' => 'Active Subscription',
                        'expired' => 'Expired Subscription',
                        'none' => 'No Subscription',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'active') {
                            return $query->whereActiveSubscribers();
                        } elseif ($value === 'expired') {
                            return $query->whereDoesntHave('subscriptions', function ($sq) {
                                $sq->where('status', 'active')->where('end_date', '>', now());
                            })->whereHas('subscriptions');
                        } elseif ($value === 'none') {
                            return $query->whereDoesntHave('subscriptions');
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('plan_status')
                    ->label('Plan Assignment Status')
                    ->options([
                        'action_required' => 'Action Required (Needs Plan / Expiring)',
                        'needs_plans' => 'Needs Plan Assignment',
                        'expiring_soon' => 'Plans Expiring Soon (≤ 3 Days)',
                        'missing_workout' => 'Needs Workout Plan',
                        'missing_meal' => 'Needs Meal Plan',
                        'fully_covered' => 'Fully Covered',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'action_required') {
                            return $query->whereActionRequired(3);
                        } elseif ($value === 'needs_plans') {
                            return $query->whereNeedsPlans();
                        } elseif ($value === 'expiring_soon') {
                            return $query->wherePlansExpiringSoon(3);
                        } elseif ($value === 'missing_workout') {
                            return $query->whereNeedsWorkoutPlan();
                        } elseif ($value === 'missing_meal') {
                            return $query->whereNeedsMealPlan();
                        } elseif ($value === 'fully_covered') {
                            return $query->whereFullyCovered(3);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('assign_plan')
                    ->label('Assign Plan')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('plan_type')
                            ->label('Plan Type')
                            ->options([
                                'workout' => 'Workout Plan',
                                'meal' => 'Meal Plan',
                            ])
                            ->default('workout')
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('plan_id')
                            ->label('Select Plan')
                            ->options(function (Forms\Get $get) {
                                $type = $get('plan_type');
                                if ($type === 'workout') {
                                    return WorkoutPlan::pluck('name', 'id')->toArray();
                                } elseif ($type === 'meal') {
                                    return MealPlan::pluck('name', 'id')->toArray();
                                }
                                return [];
                            })
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
                    ->action(function (User $record, array $data) {
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addDays(28);

                        app(PlanAssignmentService::class)->assignPlan(
                            $record,
                            (int) $data['plan_id'],
                            $data['plan_type'],
                            $startDate,
                            $endDate
                        );

                        Notification::make()
                            ->title('Plan Assigned & Materialized')
                            ->body("Assigned {$data['plan_type']} plan to {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('set_water_target')
                    ->label('Water Goal')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->fillForm(fn (User $record): array => [
                        'water_target_mode' => $record->water_target_mode ?? 'auto',
                        'admin_water_target_ml' => $record->admin_water_target_ml,
                        'water_multiplier_per_kg' => $record->water_multiplier_per_kg,
                        'admin_water_notes' => $record->admin_water_notes,
                        'allow_user_water_override' => $record->allow_user_water_override ?? true,
                    ])
                    ->form([
                        Forms\Components\Select::make('water_target_mode')
                            ->label('Target Calculation Mode')
                            ->options([
                                'auto' => 'Auto (Tier 1 Base / Tier 2 InBody + Workouts)',
                                'fixed' => 'Fixed Coach Target (ml)',
                                'custom_multiplier' => 'Custom Ratio (ml per kg)',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('admin_water_target_ml')
                            ->label('Fixed Daily Target (ml)')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue(8000)
                            ->placeholder('e.g. 3500')
                            ->visible(fn (Forms\Get $get) => $get('water_target_mode') === 'fixed')
                            ->required(fn (Forms\Get $get) => $get('water_target_mode') === 'fixed'),
                        Forms\Components\TextInput::make('water_multiplier_per_kg')
                            ->label('Water Ratio (ml / kg)')
                            ->numeric()
                            ->minValue(25)
                            ->maxValue(60)
                            ->placeholder('e.g. 40.0')
                            ->visible(fn (Forms\Get $get) => $get('water_target_mode') === 'custom_multiplier')
                            ->required(fn (Forms\Get $get) => $get('water_target_mode') === 'custom_multiplier'),
                        Forms\Components\Textarea::make('admin_water_notes')
                            ->label('Coach Instructions')
                            ->placeholder('Personal instructions for this client')
                            ->rows(2),
                        Forms\Components\Toggle::make('allow_user_water_override')
                            ->label('Allow User Custom Override')
                            ->default(true),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update([
                            'water_target_mode' => $data['water_target_mode'],
                            'admin_water_target_ml' => $data['water_target_mode'] === 'fixed' ? (int) $data['admin_water_target_ml'] : null,
                            'water_multiplier_per_kg' => $data['water_target_mode'] === 'custom_multiplier' ? (float) $data['water_multiplier_per_kg'] : null,
                            'admin_water_notes' => $data['admin_water_notes'] ?? null,
                            'allow_user_water_override' => (bool) ($data['allow_user_water_override'] ?? true),
                        ]);

                        // Recalculate today's water log target if it exists
                        $todayLog = $record->todayWaterLog;
                        if ($todayLog) {
                            $calc = app(WaterIntakeService::class)->calculateDailyTarget($record);
                            $todayLog->target_ml = $calc['target_ml'];
                            $todayLog->recalculate();
                        }

                        Notification::make()
                            ->title('Hydration Target Updated')
                            ->body("Updated daily water target for {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PlanAssignmentsRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
            RelationManagers\InBodyLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
