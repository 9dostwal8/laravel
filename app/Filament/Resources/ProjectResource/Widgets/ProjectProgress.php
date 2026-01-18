<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProjectProgress extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public ?Model $record = null;

    protected function getData(): array
    {
        /** @var Collection $progresses */
        $progresses = $this->record->progresses;

        $data = $progresses->map(function ($progress) {
            return [
                'visited_at' => $progress->visited_at->shortEnglishMonth.' '.$progress->visited_at->year,
                'percentage' => $progress->progress_percentage,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Progress percentage',
                    'data' => $progresses->isEmpty() ? [] : Arr::pluck($data, 'percentage'),
                ],
            ],
            'labels' => $progresses->isEmpty() ? [] : Arr::pluck($data, 'visited_at'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
