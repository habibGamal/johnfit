<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepsPresetResource\Pages;
use App\Filament\Resources\RepsPresetResource\RelationManagers;
use App\Models\RepsPreset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepsPresetResource extends Resource
{
    protected static ?string $model = RepsPreset::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('reps')
                    ->schema([
                        Forms\Components\TextInput::make('count')
                            ->numeric()
                            ->integer()
                            ->required(),
                    ])->defaultItems(4)
                    ->grid(4)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('short_name')
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
            'index' => Pages\ListRepsPresets::route('/'),
            'create' => Pages\CreateRepsPreset::route('/create'),
            'edit' => Pages\EditRepsPreset::route('/{record}/edit'),
        ];
    }
}
