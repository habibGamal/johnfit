<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealResource\Pages;
use App\Filament\Resources\MealResource\RelationManagers;
use App\Models\Meal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->multiple()
                    ->options([
                        'breakfast' => 'Breakfast',
                        'lunch' => 'Lunch',
                        'dinner' => 'Dinner',
                        'snack' => 'Snack',
                        'pre-workout' => 'Pre-Workout',
                        'post-workout' => 'Post-Workout',
                    ])
                    ->required(),
                Forms\Components\Section::make('Meal Macros')
                    ->description('Macros are calories , proteins, fats and carbs (per 1g).')
                    ->schema([
                        Forms\Components\TextInput::make('meal_macros.calories')
                            ->numeric()
                            ->required()
                            ->label('Calories'),
                        Forms\Components\TextInput::make('meal_macros.proteins')
                            ->numeric()
                            ->required()
                            ->label('Proteins'),
                        Forms\Components\TextInput::make('meal_macros.fats')
                            ->numeric()
                            ->required()
                            ->label('Fats'),
                        Forms\Components\TextInput::make('meal_macros.carbs')
                            ->numeric()
                            ->required()
                            ->label('Carbs'),
                    ]),

                Forms\Components\Repeater::make('vitamins')
                    ->schema([
                        Forms\Components\Select::make('key')
                            ->options([
                                "Ash" => "Ash",
                                "Iron" => "Iron",
                                "Zinc" => "Zinc",
                                "Fiber" => "Fiber",
                                "Water" => "Water",
                                "Copper" => "Copper",
                                "Niacin" => "Niacin",
                                "Refuse" => "Refuse",
                                "Sodium" => "Sodium",
                                "Betaine" => "Betaine",
                                "Calcium" => "Calcium",
                                "Choline" => "Choline",
                                "Fluoride" => "Fluoride",
                                "Selenium" => "Selenium",
                                "Magnesium" => "Magnesium",
                                "Manganese" => "Manganese",
                                "Potassium" => "Potassium",
                                "Vitamin A" => "Vitamin A",
                                "Vitamin C" => "Vitamin C",
                                "Vitamin D" => "Vitamin D",
                                "Vitamin E" => "Vitamin E",
                                "Vitamin K" => "Vitamin K",
                                "Folic Acid" => "Folic Acid",
                                "Vitamin B1" => "Vitamin B1",
                                "Vitamin B2" => "Vitamin B2",
                                "Vitamin B5" => "Vitamin B5",
                                "Vitamin B6" => "Vitamin B6",
                                "Phosphorous" => "Phosphorous",
                                "Vitamin B12" => "Vitamin B12",
                            ])
                            ->required()
                            ->distinct()
                            ->label('Vitamin Name'),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->label('Amount'),
                    ])
                    ->required()
                    ->label('Vitamins')
                    ->reorderable(false)
                    ->columns(2)->grid(3)

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'edit' => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
