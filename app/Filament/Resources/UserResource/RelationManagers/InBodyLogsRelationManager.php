<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InBodyLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'inBodyLogs';

    protected static ?string $title = 'InBody Logs';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Measurement Info')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('measured_at')
                            ->label('Date')
                            ->dateTime('M d, Y'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Body Composition')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('weight')
                            ->label('Weight')
                            ->suffix(' kg')
                            ->numeric(2),
                        TextEntry::make('smm')
                            ->label('Skeletal Muscle Mass')
                            ->suffix(' kg')
                            ->numeric(2),
                        TextEntry::make('lean_body_mass')
                            ->label('Lean Body Mass')
                            ->suffix(' kg')
                            ->numeric(2)
                            ->placeholder('—'),
                        TextEntry::make('pbf')
                            ->label('Body Fat %')
                            ->suffix('%')
                            ->numeric(1),
                        TextEntry::make('body_water')
                            ->label('Body Water')
                            ->suffix(' L')
                            ->numeric(2)
                            ->placeholder('—'),
                        TextEntry::make('visceral_fat')
                            ->label('Visceral Fat')
                            ->numeric(1)
                            ->placeholder('—'),
                    ]),

                Section::make('Health Indices')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('bmi')
                            ->label('BMI')
                            ->numeric(1),
                        TextEntry::make('bmr')
                            ->label('BMR')
                            ->suffix(' kcal')
                            ->numeric(0),
                        TextEntry::make('waist_hip_ratio')
                            ->label('Waist-Hip Ratio')
                            ->numeric(3)
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('measured_at')
            ->defaultSort('measured_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('measured_at')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Weight (kg)')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('smm')
                    ->label('SMM (kg)')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('pbf')
                    ->label('Body Fat %')
                    ->numeric(1)
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('bmi')
                    ->label('BMI')
                    ->numeric(1),
                Tables\Columns\TextColumn::make('bmr')
                    ->label('BMR (kcal)')
                    ->numeric(0),
                Tables\Columns\TextColumn::make('lean_body_mass')
                    ->label('Lean Mass (kg)')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('body_water')
                    ->label('Body Water (L)')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('visceral_fat')
                    ->label('Visceral Fat')
                    ->numeric(1)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('waist_hip_ratio')
                    ->label('WHR')
                    ->numeric(3)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
