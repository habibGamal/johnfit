<?php

namespace App\Filament\Resources;

use App\Enums\AssessmentType;
use App\Filament\Resources\AssessmentResource\Pages;
use App\Models\Assessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Assessments';

    protected static ?string $modelLabel = 'Assessment Question';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('assessment-images')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->options(collect(AssessmentType::cases())->mapWithKeys(
                                fn (AssessmentType $type) => [$type->value => $type->label()]
                            ))
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('options')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->visible(fn (Get $get): bool => in_array(
                                $get('type'),
                                [AssessmentType::Select->value, AssessmentType::MultipleSelect->value]
                            ))
                            ->addActionLabel('Add Option')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->limit(60),
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (AssessmentType $state): string => $state->label())
                    ->colors([
                        'primary' => AssessmentType::Text->value,
                        'success' => AssessmentType::Select->value,
                        'warning' => AssessmentType::MultipleSelect->value,
                    ]),
                Tables\Columns\IconColumn::make('image')
                    ->boolean()
                    ->label('Has Image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit' => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }
}
