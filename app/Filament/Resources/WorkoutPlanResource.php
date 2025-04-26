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
                    Forms\Components\Repeater::make('days')->schema([
                        Forms\Components\TextInput::make('day')
                            ->datalist([
                                'Monday',
                                'Tuesday',
                                'Wednesday',
                                'Thursday',
                                'Friday',
                                'Saturday',
                                'Sunday',
                            ])
                            ->required(),
                        Forms\Components\Repeater::make('workouts')->schema([
                            Forms\Components\Placeholder::make('_placeholder')->label('Workout Details')
                                ->content(function (Get $get) use ($workouts) {
                                    $workout = $workouts->where('id', '=', $get('workout_id'))->first();
                                    if (!$workout) {
                                        return new HtmlString('<p>Workout not choosen yet</p>');
                                    }
                                    return view('placeholders.workout_details', ['workout' => $workout]);
                                }),
                            Forms\Components\Select::make('workout_id')
                                ->label('Workout')
                                ->options($workouts->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->live(onBlur: true)
                                ->required(),
                            Forms\Components\Select::make('reps')
                                ->label('Reps')
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
                        ])->defaultItems(1),
                    ])->defaultItems(1),
                ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
