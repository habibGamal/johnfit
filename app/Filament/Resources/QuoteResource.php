<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use App\Models\QuoteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Quotes';

    protected static ?string $modelLabel = 'Quote';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quote Details')
                    ->schema([
                        Forms\Components\Textarea::make('quote')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width(60),

                Tables\Columns\TextColumn::make('quote')
                    ->limit(80)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_next')
                    ->label('Next')
                    ->boolean()
                    ->tooltip('Scheduled as next quote to send')
                    ->state(function (Quote $record): bool {
                        $setting = QuoteSetting::instance();

                        return $setting->next_quote_id === $record->id;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('set_as_next')
                    ->label('Set as Next')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->action(function (Quote $record): void {
                        QuoteSetting::instance()->update(['next_quote_id' => $record->id]);

                        Notification::make()
                            ->title('Quote scheduled')
                            ->body("Quote #{$record->id} will be sent next.")
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
