<?php

namespace App\Filament\Resources;

use App\Enums\BadgeMetric;
use App\Enums\BadgeTier;
use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Data';

    protected static ?string $navigationLabel = 'Achievement Badges';

    protected static ?string $modelLabel = 'Badge';

    protected static ?string $pluralModelLabel = 'Badges';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Badge Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Stable identifier used by seeds and deep links.'),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->maxLength(500),

                        Forms\Components\TextInput::make('icon')
                            ->required()
                            ->default('Trophy')
                            ->helperText('Lucide icon name, e.g. Trophy, Flame, Dumbbell, Crown.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Rules')
                    ->description('A badge is earned only when EVERY requirement below is satisfied.')
                    ->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('metric')
                                    ->required()
                                    ->options(BadgeMetric::options())
                                    ->native(false),

                                Forms\Components\Select::make('operator')
                                    ->required()
                                    ->default('gte')
                                    ->options([
                                        'gte' => 'At least (>=)',
                                        'lte' => 'At most (<=)',
                                    ])
                                    ->native(false),

                                Forms\Components\TextInput::make('threshold')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add requirement')
                            ->reorderable(false),
                    ]),

                Forms\Components\Section::make('Display')
                    ->schema([
                        Forms\Components\Select::make('tier')
                            ->required()
                            ->options(BadgeTier::options())
                            ->default(BadgeTier::Bronze->value),

                        Forms\Components\TextInput::make('category')
                            ->required()
                            ->default('general')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive badges are hidden from the journey and no longer awarded.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Badge $record) => $record->description)
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('tier')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('requirements_count')
                    ->label('Rules')
                    ->counts('requirements')
                    ->suffix(fn (Badge $record) => ' -> ' . $record->requirements
                        ->map(fn ($requirement) => $requirement->metric->value . ' ' . $requirement->operator . ' ' . (float) $requirement->threshold)
                        ->implode(', '))
                    ->wrap(),

                Tables\Columns\TextColumn::make('unlockers_count')
                    ->label('Unlocked by')
                    ->counts('unlockers')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->options(BadgeTier::options()),
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn () => Badge::query()->distinct()->pluck('category', 'category')->all()),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['requirements', 'unlockers']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
