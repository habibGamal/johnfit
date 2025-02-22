<?php

namespace App\Filament\User\Pages;

use App\Models\WorkoutCompletion;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\DB;

class WorkoutStatistics extends Page
{
    use HasFiltersForm;

    protected static string $view = 'filament.user.pages.workout-statistics';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'أدائي التدريبي | My Performance';
    protected static ?string $title = 'أدائي التدريبي | My Performance';
    protected static ?string $slug = 'workout-statistics';
    protected static ?int $navigationSort = 2;

    public $startDate;
    public $endDate;

    public function mount(): void
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    DatePicker::make('startDate')
                        ->label('من تاريخ | From Date')
                        ->default(now()->subMonth())
                        ->required()
                        ->afterStateUpdated(function ($state) {
                            $this->startDate = $state;
                            $this->updateWidgets();
                        }),

                    DatePicker::make('endDate')
                        ->label('إلى تاريخ | To Date')
                        ->default(now())
                        ->required()
                        ->afterStateUpdated(function ($state) {
                            $this->endDate = $state;
                            $this->updateWidgets();
                        }),
                ]),
        ];
    }

    protected function updateWidgets(): void
    {
        $this->dispatch('filtersUpdated', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WorkoutStatistics\Widgets\WorkoutStatsOverview::class,
            WorkoutStatistics\Widgets\WorkoutCompletionChart::class,
            WorkoutStatistics\Widgets\ActiveDaysWidget::class,
        ];
    }
}
