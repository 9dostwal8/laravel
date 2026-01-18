<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByYearCapitalResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.report-by-year-capitals';

    public static function getPluralLabel(): ?string
    {
        return trans('resources.year_capital_report');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::customReportQuery()
            ->with(['projectVariants']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Reports\ReportByYearCapitalResource\Pages\ListReportByYearCapitals::route('/'),
        ];
    }
}
