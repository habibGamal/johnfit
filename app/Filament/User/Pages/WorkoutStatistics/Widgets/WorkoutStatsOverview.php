<?php

namespace App\Filament\User\Pages\WorkoutStatistics\Widgets;

use App\Models\WorkoutCompletion;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class WorkoutStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';
    
    public $startDate;
    public $endDate;

    protected $listeners = ["filtersUpdated" => "updateFilters"];

    public function updateFilters($filters)
    {
        $this->startDate = $filters['startDate'] ?? now()->subMonth()->format('Y-m-d');
        $this->endDate = $filters['endDate'] ?? now()->format('Y-m-d');
    }

    protected function getStats(): array
    {
        $totalWorkouts = WorkoutCompletion::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->count();

        $completedWorkouts = WorkoutCompletion::query()
            ->where('user_id', auth()->id())
            ->where('completed', true)
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->count();

        $completionRate = $totalWorkouts > 0
            ? round(($completedWorkouts / $totalWorkouts) * 100, 1)
            : 0;

        return [
            Stat::make('إجمالي التمارين | Total Workouts', $totalWorkouts)
                ->description('العدد الكلي للتمارين المسندة')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),

            Stat::make('التمارين المكتملة | Completed Workouts', $completedWorkouts)
                ->description('عدد التمارين المكتملة')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('نسبة الإنجاز | Completion Rate', $completionRate . '%')
                ->description('نسبة إكمال التمارين')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger'))
        ];
    }
}
