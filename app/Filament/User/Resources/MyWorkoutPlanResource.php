<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\MyWorkoutPlanResource\Pages;
use App\Models\Workout;
use App\Models\WorkoutCompletion;
use App\Models\WorkoutPlan;
use App\Services\WorkoutPlanServices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Facades\FilamentView;

class MyWorkoutPlanResource extends Resource
{
    protected static ?string $model = WorkoutPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'My Workout Plans';

    public static function form(Form $form): Form
    {
        $workoutPlan = $form->getRecord();
        $planData = null;
        $workouts = null;

        if ($workoutPlan) {
            $planData = app(WorkoutPlanServices::class)->loadDataFromJsonFile($workoutPlan->file_path);
            $workoutIds = collect($planData)->pluck('workouts.*.workout_id')->flatten()->unique();
            $workouts = Workout::findMany($workoutIds)->keyBy('id');
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Workout Plan Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Plan Name')
                            ->disabled(),
                        // Forms\Components\ViewField::make('preview')
                        //     ->label('Plan Preview')
                        //     ->view('filament.user.my-workout-plan.pdf-preview')
                        //     ->viewData(['id' => $workoutPlan?->id, 'url' => '/workout-plan']),
                    ]),

                Forms\Components\Repeater::make('days')
                    ->label(false)
                    ->schema([
                        Forms\Components\TextInput::make('day')
                            ->disabled(),
                        Forms\Components\Repeater::make('workouts')
                            ->label(false)
                            ->schema([
                                Forms\Components\Checkbox::make('completed')
                                    ->label('Completed')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get) use ($workoutPlan) {
                                        WorkoutCompletion::updateOrCreate(
                                            [
                                                'user_id' => auth()->id(),
                                                'workout_plan_id' => $workoutPlan->id,
                                                'day' => $get('../../day'),
                                                'workout_id' => $get('workout_id'),
                                            ],
                                            ['completed' => $state]
                                        );
                                    })
                                    ->dehydrated(false),
                                Forms\Components\Placeholder::make('_placeholder')
                                    ->label('Workout Details')
                                    ->content(function ($get) {
                                        $workout = Workout::find($get('workout_id'));
                                        return view('user.workout-repeater-details', [
                                            'workout' => $workout
                                        ]);
                                    }),
                                Forms\Components\TextInput::make('reps')
                                    ->disabled()
                                    ->label('Repetitions'),
                                Forms\Components\TextInput::make('workout_id')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->hidden(),
                            ])->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                    ])
                    ->afterStateHydrated(function ($component, $state) use ($workoutPlan) {
                        if (!$workoutPlan)
                            return;

                        $json = Storage::disk('local')->get($workoutPlan->file_path);
                        $days = json_decode($json, true);

                        // Load completion states
                        if ($days) {
                            foreach ($days as &$day) {
                                foreach ($day['workouts'] as &$workout) {
                                    $completion = WorkoutCompletion::where([
                                        'user_id' => auth()->id(),
                                        'workout_plan_id' => $workoutPlan->id,
                                        'day' => $day['day'],
                                        'workout_id' => $workout['workout_id'],
                                    ])->first();

                                    $workout['completed'] = $completion?->completed ?? false;
                                }
                            }
                        }

                        $component->state($days);
                    })
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Assigned Date')
                    ->date()
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('users', function ($query) {
                $query->where('users.id', auth()->id());
            }))
            ->filters([
                //
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyWorkoutPlans::route('/'),
            'create' => Pages\CreateMyWorkoutPlan::route('/create'),
            'view' => Pages\ViewMyWorkoutPlan::route('/{record}'),
            'edit' => Pages\EditMyWorkoutPlan::route('/{record}/edit'),
        ];
    }
}
