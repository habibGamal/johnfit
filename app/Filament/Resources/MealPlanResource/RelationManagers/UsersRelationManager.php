<?php

namespace App\Filament\Resources\MealPlanResource\RelationManagers;

use App\Models\User;
use App\Models\UserPlanAssignment;
use App\Services\PlanAssignmentService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Assigned Users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $set('end_date', Carbon::parse($state)->addDays(28)->toDateString());
                        }
                    }),
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date (4 Weeks / 28 Days)')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign_user')
                    ->label('Assign User')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->options(User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('end_date', Carbon::parse($state)->addDays(28)->toDateString());
                                }
                            }),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date (4 Weeks / 28 Days)')
                            ->default(now()->addDays(28)->toDateString())
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->action(function (array $data) {
                        $user = User::findOrFail($data['user_id']);
                        $mealPlan = $this->getOwnerRecord();
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addDays(28);

                        app(PlanAssignmentService::class)->assignPlan($user, $mealPlan->id, 'meal', $startDate, $endDate);

                        Notification::make()
                            ->title('User Assigned')
                            ->body("Assigned {$mealPlan->name} to {$user->name}.")
                            ->success()
                            ->send();
                    }),
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
}

