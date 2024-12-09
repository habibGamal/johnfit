<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class DietPlanLevelsChart extends ChartWidget
{
    public $id;

    public $title;

    public $target_value;

    public $current_value;

    #[On('update-target-value')]
    public function updateTargetValue($id, $value)
    {
        if ($this->id == $id) {
            $this->target_value = $value;
        }
    }

    #[On('update-current-value')]
    public function updateCurrentValue($id, $value)
    {
        if ($this->id == $id) {
            $this->current_value = $value;
        }
    }

    public function getHeading(): ?string
    {
        return $this->title . ': ' . $this->current_value . ' / ' . $this->target_value;
    }

    protected function getData(): array
    {
        $current_percentage = $this->target_value > 0 ? $this->current_value / $this->target_value : 0;
        $target_percentage = 1 - $current_percentage > 0 ? 1 - $current_percentage : 0;
        return [
            'datasets' => [
                [
                    'data' => [
                        $target_percentage * $this->target_value,
                        $current_percentage * $this->target_value,
                    ],
                    'backgroundColor' => [
                        '#c1c1c1',
                        'rgb(75, 192, 192)',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
