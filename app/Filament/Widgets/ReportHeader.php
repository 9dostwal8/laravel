<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;

class ReportHeader extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static string $view = 'reports.header';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
