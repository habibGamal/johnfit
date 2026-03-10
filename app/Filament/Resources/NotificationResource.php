<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\SentNotification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = SentNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('body')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'info' => 'Info',
                                'success' => 'Success',
                                'warning' => 'Warning',
                                'error' => 'Error',
                            ])
                            ->default('info')
                            ->required(),

                        Forms\Components\Select::make('target_type')
                            ->label('Send To')
                            ->options([
                                'all' => 'All Users',
                                'specific' => 'Specific Users',
                            ])
                            ->default('all')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('target_user_ids')
                            ->label('Select Users')
                            ->multiple()
                            ->options(fn () => User::query()->where('role', '!=', 'admin')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('target_type') === 'specific')
                            ->required(fn (Get $get) => $get('target_type') === 'specific')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'error',
                    ]),

                Tables\Columns\TextColumn::make('target_type')
                    ->label('Sent To')
                    ->formatStateUsing(fn (string $state, SentNotification $record): string => match ($state) {
                        'all' => 'All Users',
                        'specific' => count($record->target_user_ids ?? []).' User(s)',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Sent By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'error' => 'Error',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view' => Pages\ViewNotification::route('/{record}'),
        ];
    }
}
