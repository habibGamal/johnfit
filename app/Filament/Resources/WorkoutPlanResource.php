<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkoutPlanResource\Pages;
use App\Filament\Resources\WorkoutPlanResource\RelationManagers;
use App\Models\WorkoutPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class WorkoutPlanResource extends Resource
{
    protected static ?string $model = WorkoutPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $workouts = \App\Models\Workout::all();
        return $form
            ->schema([
                Forms\Components\ViewField::make('preview')
                    ->disabled()
                    ->view('pdf_templates.iframe', [
                        'id' => $form->getRecord()?->id,
                        'url' => '/workout-plan'
                    ]),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('generate_pdf')
                        ->label('Generate PDF')
                        ->disabled(!$form->getRecord())
                        ->url(function(?WorkoutPlan $workoutPlan) {
                            if (!$workoutPlan->id) {
                                return null;
                            }
                            return route('workout-plan.download', $workoutPlan);
                        })
                        ->openUrlInNewTab()
                ]),
                Forms\Components\TextInput::make('name')
                    ->label('Workout Plan Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Section::make('Plan')->schema([
                    Forms\Components\Repeater::make('days')
                        ->itemLabel(fn (array $state): ?string => $state['day'] ?? null)
                        ->schema([
                            Forms\Components\Select::make('day')
                                ->label('Day')
                                ->options(collect(range(1, 30))->mapWithKeys(fn ($i) => ["Day {$i}" => "Day {$i}"]))
                                ->default(fn (Get $get) => 'Day ' . (count($get('../../days') ?? []) + 1))
                                ->required(),
                            Forms\Components\Repeater::make('workouts')
                                ->label('Exercise Slots')
                                ->addActionLabel('Add Exercise Slot')
                                ->schema([
                                    Forms\Components\Repeater::make('options')
                                        ->label('Options (choose ONE — add more for OR alternatives)')
                                        ->addActionLabel('Add OR Alternative')
                                        ->minItems(1)
                                        ->schema([
                                            Forms\Components\Placeholder::make('_placeholder')->label('Workout Details')
                                                ->content(function (Get $get) use ($workouts) {
                                                    $workout = $workouts->where('id', '=', $get('workout_id'))->first();
                                                    if (!$workout) {
                                                        return new HtmlString('<p class="text-sm text-gray-500">Workout not chosen yet</p>');
                                                    }
                                                    return view('placeholders.workout_details', ['workout' => $workout]);
                                                }),
                                            Forms\Components\Select::make('workout_id')
                                                ->label('Workout Exercise')
                                                ->options($workouts->pluck('name', 'id')->toArray())
                                                ->searchable()
                                                ->live(onBlur: true)
                                                ->required(),
                                            Forms\Components\Select::make('reps')
                                                ->label('Reps Preset')
                                                ->options(\App\Models\RepsPreset::all()->pluck('short_name', 'id')->toArray())
                                                ->createOptionForm([
                                                    Forms\Components\Repeater::make('reps')
                                                        ->schema([
                                                            Forms\Components\TextInput::make('count')
                                                                ->label('Count')
                                                                ->numeric()
                                                                ->integer()
                                                                ->required(),
                                                        ])->defaultItems(4)->grid(4),
                                                ])
                                                ->createOptionUsing(function (array $data): int {
                                                    return \App\Models\RepsPreset::create($data)->getKey();
                                                })
                                                ->required(),
                                        ])
                                        ->defaultItems(1),
                                ])
                                ->defaultItems(1),
                        ])->defaultItems(1),
                ]),
                Forms\Components\Section::make('Schedule Sync Strategy')
                    ->description('Choose how saving changes to this plan affects active user schedules.')
                    ->schema([
                        Forms\Components\Radio::make('update_strategy')
                            ->label('Update Strategy')
                            ->options([
                                'future_only' => 'Future Days Only (Recommended — drops and re-materializes future days starting tomorrow)',
                                'today_and_future' => 'Today & Future Days (Re-materializes uncompleted items today and future days)',
                                'none' => 'Template Only (Keep existing user schedules unchanged)',
                            ])
                            ->default('future_only')
                            ->dehydrated(false),
                    ])
                    ->visible(fn (?WorkoutPlan $record) => $record && $record->assignments()->where('status', 'active')->exists()),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignments_count')
                    ->label('Assigned Users')
                    ->counts('assignments')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('assign_to_user')
                    ->label('Assign User')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->options(\App\Models\User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('end_date', \Carbon\Carbon::parse($state)->addDays(28)->toDateString());
                                }
                            }),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date (4 Weeks / 28 Days)')
                            ->default(now()->addDays(28)->toDateString())
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->action(function (WorkoutPlan $record, array $data) {
                        $user = \App\Models\User::findOrFail($data['user_id']);
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addDays(28);
                        app(\App\Services\PlanAssignmentService::class)->assignPlan($user, $record->id, 'workout', $startDate, $endDate);
                        \Filament\Notifications\Notification::make()
                            ->title('Workout Plan Assigned')
                            ->body("Assigned {$record->name} to {$user->name} (4 weeks) and materialized daily schedules.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkoutPlans::route('/'),
            'create' => Pages\CreateWorkoutPlan::route('/create'),
            'edit' => Pages\EditWorkoutPlan::route('/{record}/edit'),
            'view' => Pages\ViewWorkoutPlan::route('/{record}'),
        ];
    }
}
