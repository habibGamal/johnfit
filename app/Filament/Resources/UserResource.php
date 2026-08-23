<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('role')
                    ->required()
                    ->maxLength(255)
                    ->default('user'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('User Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'admin' ? 'danger' : 'primary'),
                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not verified'),
                        TextEntry::make('assessment_completed_at')
                            ->label('Assessment Completed')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not completed'),
                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->dateTime('M d, Y'),
                    ]),

                Section::make('Assessment Answers')
                    ->description('Answers submitted by the user during the initial assessment.')
                    ->schema([
                        RepeatableEntry::make('assessmentAnswers')
                            ->label('')
                            ->schema([
                                TextEntry::make('assessment.question')
                                    ->label('Question')
                                    ->weight('semibold'),
                                TextEntry::make('answer')
                                    ->label('Answer')
                                    ->formatStateUsing(
                                        fn (mixed $state): string => is_array($state)
                                            ? implode(', ', $state)
                                            : (string) $state
                                    ),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->visible(fn (User $record): bool => $record->hasCompletedAssessment()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
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
                Tables\Actions\Action::make('assign_plan')
                    ->label('Assign Plan')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('plan_type')
                            ->label('Plan Type')
                            ->options([
                                'workout' => 'Workout Plan',
                                'meal' => 'Meal Plan',
                            ])
                            ->default('workout')
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('plan_id')
                            ->label('Select Plan')
                            ->options(function (Forms\Get $get) {
                                $type = $get('plan_type');
                                if ($type === 'workout') {
                                    return \App\Models\WorkoutPlan::pluck('name', 'id')->toArray();
                                } elseif ($type === 'meal') {
                                    return \App\Models\MealPlan::pluck('name', 'id')->toArray();
                                }
                                return [];
                            })
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
                    ->action(function (User $record, array $data) {
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addDays(28);

                        app(\App\Services\PlanAssignmentService::class)->assignPlan(
                            $record,
                            (int) $data['plan_id'],
                            $data['plan_type'],
                            $startDate,
                            $endDate
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Plan Assigned & Schedules Materialized')
                            ->body("Assigned {$data['plan_type']} plan to {$record->name}.")
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
            RelationManagers\PlanAssignmentsRelationManager::class,
            RelationManagers\InBodyLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
