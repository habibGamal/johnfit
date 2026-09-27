<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Subscriptions History & Management';

    protected static ?string $icon = 'heroicon-o-credit-card';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_id')
                    ->label('Subscription Plan')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $set('tier_id', null);
                        if ($state) {
                            $plan = SubscriptionPlan::find($state);
                            $startDate = $get('start_date') ? Carbon::parse($get('start_date')) : now();
                            if ($plan && $plan->duration_days) {
                                $set('end_date', $startDate->copy()->addDays($plan->duration_days)->toDateTimeString());
                            }
                        }
                    }),
                Forms\Components\Select::make('tier_id')
                    ->label('Duration Tier')
                    ->options(function (Get $get) {
                        $planId = $get('plan_id');
                        if (! $planId) return [];
                        $plan = SubscriptionPlan::with('activeTiers')->find($planId);
                        if (! $plan) return [];
                        return $plan->activeTiers->mapWithKeys(function ($tier) {
                            $label = "{$tier->months} Month" . ($tier->months > 1 ? 's' : '') . " ({$tier->price} EGP)";
                            if ($tier->tag) $label .= " - {$tier->tag}";
                            return [$tier->id => $label];
                        })->toArray();
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $tier = \App\Models\SubscriptionPlanTier::find($state);
                            if ($tier) {
                                $set('duration_months', $tier->months);
                                $set('duration_days', $tier->effective_days);
                                $startDate = $get('start_date') ? Carbon::parse($get('start_date')) : now();
                                $set('end_date', $startDate->copy()->addDays($tier->effective_days)->toDateTimeString());
                            }
                        }
                    }),
                Forms\Components\TextInput::make('duration_months')
                    ->label('Months')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Days')
                    ->numeric()
                    ->default(30),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Start Date & Time')
                    ->default(now())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $tierId = $get('tier_id');
                        if ($tierId) {
                            $tier = \App\Models\SubscriptionPlanTier::find($tierId);
                            if ($tier && $state) {
                                $set('end_date', Carbon::parse($state)->addDays($tier->effective_days)->toDateTimeString());
                                return;
                            }
                        }
                        $planId = $get('plan_id');
                        if ($state && $planId) {
                            $plan = SubscriptionPlan::find($planId);
                            if ($plan && $plan->duration_days) {
                                $set('end_date', Carbon::parse($state)->addDays($plan->duration_days)->toDateTimeString());
                            }
                        }
                    }),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('End Date & Time')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Subscription Plan')
                    ->weight('semibold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.price')
                    ->label('Price')
                    ->money('USD')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'expired',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Validity')
                    ->state(function ($record): string {
                        if ($record->status !== 'active') {
                            return ucfirst($record->status);
                        }
                        if (! $record->end_date) {
                            return 'Lifetime';
                        }
                        if ($record->end_date->isPast()) {
                            return 'Expired ' . $record->end_date->diffForHumans();
                        }
                        return $record->end_date->diffForHumans(now(), ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]);
                    })
                    ->badge()
                    ->color(function ($record): string {
                        if ($record->status !== 'active') {
                            return 'gray';
                        }
                        if ($record->end_date && $record->end_date->isPast()) {
                            return 'danger';
                        }
                        if ($record->end_date && $record->end_date->diffInDays(now()) <= 3) {
                            return 'warning';
                        }
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Subscription')
                    ->icon('heroicon-o-plus-circle'),
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
