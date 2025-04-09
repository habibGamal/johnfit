<?php

namespace App\Filament\User\Pages\WorkoutStatistics\Widgets;

use App\Models\WorkoutCompletion;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkoutCompletionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = null;
    protected int | string | array $columnSpan = ['lg' => 2];

    public $startDate;
    public $endDate;

    protected $listeners = ["filtersUpdated" => "updateFilters"];

    public function getHeading(): string
    {
        return __('workout.statistics.workout_activity');
    }

    public function updateFilters($filters)
    {
        $this->startDate = $filters['startDate'] ?? now()->subMonth()->format('Y-m-d');
        $this->endDate = $filters['endDate'] ?? now()->format('Y-m-d');
    }

    protected function getData(): array
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $dailyStats = WorkoutCompletion::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($stat) {
                return [
                    'date' => Carbon::parse($stat->date)->format('Y-m-d'),
                    'total' => $stat->total,
                    'completed' => $stat->completed,
                    'rate' => round(($stat->completed / $stat->total) * 100, 1)
                ];
            });

        DB::statement("SET SESSION sql_mode=(SELECT CONCAT(@@sql_mode, ',ONLY_FULL_GROUP_BY'));");

        return [
            'datasets' => [
                [
                    'label' => __('workout.statistics.total_workouts'),
                    'data' => $dailyStats->pluck('total')->toArray(),
                    'borderColor' => '#6B7280',
                    'backgroundColor' => '#6B728040',
                    'fill' => true,
                ],
                [
                    'label' => __('workout.statistics.completed_workouts'),
                    'data' => $dailyStats->pluck('completed')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => '#10B98140',
                    'fill' => true,
                ],
            ],
            'labels' => $dailyStats->map(function ($stat) {
                return Carbon::parse($stat['date'])->translatedFormat('D j M');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
