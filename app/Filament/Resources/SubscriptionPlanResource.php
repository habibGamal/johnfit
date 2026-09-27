<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->label('Base Price (1 Month fallback)')
                            ->required()
                            ->numeric()
                            ->prefix('EGP')
                            ->minValue(0),
                        Forms\Components\TextInput::make('tag')
                            ->maxLength(255)
                            ->placeholder('e.g. Most Popular, Best Value')
                            ->hint('Optional badge shown on plan card'),
                        Forms\Components\TextInput::make('duration_days')
                            ->label('Base Duration (Days)')
                            ->required()
                            ->numeric()
                            ->default(30)
                            ->suffix('days')
                            ->minValue(1),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Duration & Pricing Tiers')
                    ->description('Dynamically configure duration options (e.g. 1 month, 3 months, 6 months) and their respective prices.')
                    ->schema([
                        Forms\Components\Repeater::make('tiers')
                            ->relationship('tiers')
                            ->schema([
                                Forms\Components\TextInput::make('months')
                                    ->label('Months')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->placeholder('1, 3, 6, 12...'),
                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('EGP')
                                    ->minValue(0),
                                Forms\Components\TextInput::make('tag')
                                    ->label('Badge / Tag')
                                    ->placeholder('e.g. Save 15%'),
                                Forms\Components\TextInput::make('duration_days')
                                    ->label('Days')
                                    ->numeric()
                                    ->placeholder('Auto (months * 30)')
                                    ->helperText('Empty = months * 30'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(5)
                            ->addActionLabel('Add Duration Tier')
                            ->reorderable('order')
                            ->collapsible()
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\Repeater::make('features')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->addActionLabel('Add Feature')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Base Price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tiers_summary')
                    ->label('Duration Tiers')
                    ->state(function (SubscriptionPlan $record): string {
                        $tiers = $record->activeTiers;
                        if ($tiers->isEmpty()) {
                            return 'No tiers (using base price)';
                        }

                        return $tiers->map(fn ($t) => "{$t->months}M: {$t->price} EGP")->join(' | ');
                    }),
                Tables\Columns\TextColumn::make('tag')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
