<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanTier;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->columns(2)->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('plan_id')
                        ->relationship('plan', 'name')
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $set('tier_id', null);
                        }),
                    Forms\Components\Select::make('tier_id')
                        ->label('Duration Tier')
                        ->options(function (Forms\Get $get) {
                            $planId = $get('plan_id');
                            if (! $planId) {
                                return [];
                            }
                            $plan = SubscriptionPlan::with('activeTiers')->find($planId);
                            if (! $plan) {
                                return [];
                            }

                            return $plan->activeTiers->mapWithKeys(function ($tier) {
                                $label = "{$tier->months} Month".($tier->months > 1 ? 's' : '')." ({$tier->price} EGP)";
                                if ($tier->tag) {
                                    $label .= " - {$tier->tag}";
                                }

                                return [$tier->id => $label];
                            })->toArray();
                        })
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if ($state) {
                                $tier = SubscriptionPlanTier::find($state);
                                if ($tier) {
                                    $set('duration_months', $tier->months);
                                    $set('duration_days', $tier->effective_days);
                                    $startDate = $get('start_date') ? Carbon::parse($get('start_date')) : now();
                                    $set('end_date', $startDate->copy()->addDays($tier->effective_days)->toDateTimeString());
                                }
                            }
                        }),
                    Forms\Components\TextInput::make('duration_months')
                        ->label('Duration (Months)')
                        ->numeric()
                        ->default(1),
                    Forms\Components\TextInput::make('duration_days')
                        ->label('Duration (Days)')
                        ->numeric()
                        ->default(30),
                    Forms\Components\DateTimePicker::make('start_date')
                        ->default(now())
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $days = (int) ($get('duration_days') ?: 30);
                            if ($state) {
                                $set('end_date', Carbon::parse($state)->addDays($days)->toDateTimeString());
                            }
                        }),
                    Forms\Components\DateTimePicker::make('end_date'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default('pending'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duration')
                    ->formatStateUsing(function ($state, Subscription $record) {
                        $months = $state ?: ($record->tier?->months ?: 1);
                        $days = $record->duration_days ?: ($record->tier?->effective_days ?: 30);

                        return "{$months}M ({$days}d)";
                    })
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'expired',
                        'secondary' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
