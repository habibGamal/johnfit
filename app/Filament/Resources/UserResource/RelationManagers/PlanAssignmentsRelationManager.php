<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\MealPlan;
use App\Models\User;
use App\Models\UserPlanAssignment;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlanAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'planAssignments';

    protected static ?string $title = 'Plan Assignments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_type')
                    ->label('Plan Type')
                    ->options([
                        'workout' => 'Workout Plan',
                        'meal' => 'Meal Plan',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Select::make('plan_id')
                    ->label('Plan Template')
                    ->options(function (Get $get) {
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
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plan_name')
            ->columns([
                Tables\Columns\TextColumn::make('plan_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'workout' => 'primary',
                        'meal' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('plan_name')
                    ->label('Plan Name')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('workoutPlan', fn ($wq) => $wq->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('mealPlan', fn ($mq) => $mq->where('name', 'like', "%{$search}%"));
                        });
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_type')
                    ->options([
                        'workout' => 'Workout Plan',
                        'meal' => 'Meal Plan',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign_plan')
                    ->label('Assign New Plan')
                    ->icon('heroicon-o-plus-circle')
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
                            ->options(function (Get $get) {
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
                    ->action(function (array $data) {
                        /** @var User $user */
                        $user = $this->getOwnerRecord();
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addDays(28);

                        app(PlanAssignmentService::class)->assignPlan(
                            $user,
                            (int) $data['plan_id'],
                            $data['plan_type'],
                            $startDate,
                            $endDate
                        );

                        Notification::make()
                            ->title('Plan Assigned & Materialized')
                            ->body("Successfully assigned {$data['plan_type']} plan to {$user->name}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
