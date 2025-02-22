<?php

namespace App\Filament\User\Pages\WorkoutStatistics\Widgets;

use App\Models\WorkoutCompletion;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActiveDaysWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.user.pages.workout-statistics.widgets.active-days-widget';
    protected int | string | array $columnSpan = ['lg' => 1];

    protected const WEEKDAYS = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    public $startDate;
    public $endDate;
    public array $activeDays = [];

    protected $listeners = ["filtersUpdated" => "updateFilters"];

    public function updateFilters($filters): void
    {
        $this->startDate = $filters['startDate'] ?? now()->subMonth()->format('Y-m-d');
        $this->endDate = $filters['endDate'] ?? now()->format('Y-m-d');
        $this->loadActiveDays();
    }

    protected function loadActiveDays(): void
    {
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $weekdays = static::WEEKDAYS;
        $orderByCase = "FIELD(day, " . implode(',', array_fill(0, count($weekdays), '?')) . ")";

        $this->activeDays = WorkoutCompletion::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%W") as day'),
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day', 'date')
            ->orderByRaw($orderByCase, $weekdays)
            ->get()
            ->groupBy('day')
            ->map(function ($dayGroup) {
                return [
                    'day' => $dayGroup->first()->day,
                    'count' => $dayGroup->sum('count')
                ];
            })
            ->map(function ($day) {
                $arabicDays = [
                    'Sunday' => 'الأحد',
                    'Monday' => 'الإثنين',
                    'Tuesday' => 'الثلاثاء',
                    'Wednesday' => 'الأربعاء',
                    'Thursday' => 'الخميس',
                    'Friday' => 'الجمعة',
                    'Saturday' => 'السبت'
                ];

                return [
                    'label' => $arabicDays[$day['day']] . ' | ' . $day['day'],
                    'count' => (int) $day['count'],
                    'intensity' => $this->getIntensityClass((int) $day['count'])
                ];
            })
            ->values()
            ->toArray();

        DB::statement("SET SESSION sql_mode=(SELECT CONCAT(@@sql_mode, ',ONLY_FULL_GROUP_BY'));");
    }

    protected function getIntensityClass(int $count): string
    {
        return match(true) {
            $count >= 5 => 'bg-success-50',
            $count >= 3 => 'bg-warning-50',
            default => 'bg-gray-50',
        };
    }

    public function mount(): void
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadActiveDays();
    }

    public function render(): View
    {
        return view(static::$view, [
            'activeDays' => $this->activeDays
        ]);
    }
}
