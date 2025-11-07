<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealPlanResource\Pages;
use App\Filament\Resources\MealPlanResource\RelationManagers;
use App\Models\MealPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Livewire\Component as Livewire;

class MealPlanResource extends Resource
{
    protected static ?string $model = MealPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static $levels = [
        'calories' => 2000,
        'proteins' => 100,
        'carbs' => 200,
        'fats' => 50,
    ];

    public static function form(Form $form): Form
    {
        $meals = \App\Models\Meal::all();

        $levelsValues = self::calcValues(
            $meals,
            $form->getRecord()?->daysFromJson ?? []
        );
        return $form
            ->schema([
                Forms\Components\ViewField::make('preview')
                    ->disabled()
                    ->visible(fn(?MealPlan $mealPlan) => $mealPlan?->id)
                    ->view('pdf_templates.iframe', [
                        'id' => $form->getRecord()?->id,
                        'url' => '/meal-plan'
                    ]),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('generate_pdf')
                        ->label('Generate PDF')
                        ->visible(fn(?MealPlan $mealPlan) => $mealPlan?->id)
                        ->url(fn(?MealPlan $mealPlan) => $mealPlan ? route('meal-plan.download', $mealPlan) : '#')
                        ->openUrlInNewTab()
                ]),
                Forms\Components\Section::make('Levels')->columns(4)
                    ->schema([
                        Forms\Components\ViewField::make('calories')
                            ->disabled()
                            ->view('widgets.level')
                            ->viewData(
                                [
                                    'data' => [
                                        'id' => 'calories',
                                        'title' => 'Calories',
                                        'target_value' => $form->getRecord()?->targets['calories'] ?? 2000,
                                        'current_value' => $levelsValues['calories'],
                                    ]
                                ]
                            )->reactive(),
                        Forms\Components\ViewField::make('proteins')
                            ->disabled()
                            ->view('widgets.level', [
                                'data' => [
                                    'id' => 'proteins',
                                    'title' => 'Proteins',
                                    'target_value' => $form->getRecord()?->targets['proteins'] ?? 100,
                                    'current_value' => $levelsValues['proteins'],
                                ]
                            ]),
                        Forms\Components\ViewField::make('carbs')
                            ->disabled()
                            ->view('widgets.level', [
                                'data' => [
                                    'id' => 'carbs',
                                    'title' => 'Carbs',
                                    'target_value' => $form->getRecord()?->targets['carbs'] ?? 200,
                                    'current_value' => $levelsValues['carbs'],
                                ]
                            ]),
                        Forms\Components\ViewField::make('fats')
                            ->disabled()
                            ->view('widgets.level', [
                                'data' => [
                                    'id' => 'fats',
                                    'title' => 'Fats',
                                    'target_value' => $form->getRecord()?->targets['fats'] ?? 50,
                                    'current_value' => $levelsValues['fats'],
                                ]
                            ]),

                        Forms\Components\TextInput::make('targets.calories')
                            ->label('Calories')
                            ->numeric()
                            ->default(2000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Livewire $livewire, $state) {
                                $livewire->dispatch('update-target-value', 'calories', $state);
                            })
                            ->required(),
                        Forms\Components\TextInput::make('targets.proteins')
                            ->label('Proteins')
                            ->numeric()
                            ->default(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Livewire $livewire, $state) {
                                $livewire->dispatch('update-target-value', 'proteins', $state);
                            })
                            ->required(),
                        Forms\Components\TextInput::make('targets.carbs')
                            ->label('Carbs')
                            ->numeric()
                            ->default(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Livewire $livewire, $state) {
                                $livewire->dispatch('update-target-value', 'carbs', $state);
                            })
                            ->required(),
                        Forms\Components\TextInput::make('targets.fats')
                            ->label('Fats')
                            ->numeric()
                            ->default(50)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Livewire $livewire, $state) {
                                $livewire->dispatch('update-target-value', 'fats', $state);
                            })
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->label('Diet Plan Name')
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
                        Forms\Components\Repeater::make('time')
                            ->itemLabel(fn(array $state) => $state['meal_time'] . ' Time')
                            ->schema([
                                Forms\Components\TextInput::make('meal_time')
                                    ->datalist([
                                        'Breakfast',
                                        'Lunch',
                                        'Dinner',
                                        'Snack',
                                        'Before Workout',
                                        'After Workout',
                                    ])
                                    ->required(),
                                Forms\Components\Repeater::make('meals')->schema([
                                    Forms\Components\Placeholder::make('_placeholder')->label('Meal Details')
                                        ->content(function (Get $get) use ($meals) {
                                            $meal = $meals->where('id', '=', $get('meal_id'))->first();
                                            if (!$meal) {
                                                return new HtmlString('<p>Meal not choosen yet</p>');
                                            }
                                            return view('placeholders.meal_details', ['meal' => $meal, 'quantity' => $get('quantity')]);
                                        }),
                                    Forms\Components\Select::make('meal_id')
                                        ->label('Meal')
                                        ->options($meals->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->live(onBlur: true)
                                        ->required(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(100)
                                        ->live(onBlur: true)
                                        ->required(),
                                ])->defaultItems(1),
                            ])
                            ->collapsible(),
                    ])->defaultItems(1)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Livewire $livewire, $state) use ($meals) {
                            $values = self::calcValues($meals, $state);
                            $livewire->dispatch('update-current-value', 'calories', $values['calories']);
                            $livewire->dispatch('update-current-value', 'proteins', $values['proteins']);
                            $livewire->dispatch('update-current-value', 'carbs', $values['carbs']);
                            $livewire->dispatch('update-current-value', 'fats', $values['fats']);
                        }),
                ])
            ])->columns(1);
    }

    public static function calcValues($meals, $days)
    {
        $totalCalories = 0;
        $totalProteins = 0;
        $totalCarbs = 0;
        $totalFats = 0;
        $dayCount = count($days);

        foreach ($days as $day) {
            foreach ($day['time'] as $time) {
                foreach ($time['meals'] as $meal) {
                    $mealModel = $meals->where('id', '=', $meal['meal_id'])->first();
                    if (!$mealModel) continue;
                    $totalCalories += $meal['quantity'] * $mealModel->meal_macros['calories'];
                    $totalProteins += $meal['quantity'] * $mealModel->meal_macros['proteins'];
                    $totalCarbs += $meal['quantity'] * $mealModel->meal_macros['carbs'];
                    $totalFats += $meal['quantity'] * $mealModel->meal_macros['fats'];
                }
            }
        }

        // Calculate average values per day
        $avgCalories = $dayCount > 0 ? $totalCalories / $dayCount : 0;
        $avgProteins = $dayCount > 0 ? $totalProteins / $dayCount : 0;
        $avgCarbs = $dayCount > 0 ? $totalCarbs / $dayCount : 0;
        $avgFats = $dayCount > 0 ? $totalFats / $dayCount : 0;

        return [
            'calories' => round($avgCalories, 1),
            'proteins' => round($avgProteins, 1),
            'carbs' => round($avgCarbs, 1),
            'fats' => round($avgFats, 1),
        ];
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
            'index' => Pages\ListMealPlans::route('/'),
            'create' => Pages\CreateMealPlan::route('/create'),
            'edit' => Pages\EditMealPlan::route('/{record}/edit'),
        ];
    }
}
