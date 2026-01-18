<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ReportByInvestmentTypeResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.report-by-investment-type';

    public static function getPluralLabel(): ?string
    {
        return trans('resources.investment_type');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Reports\ReportByInvestmentTypeResource\Pages\ListReportByInvestmentType::route('/'),
        ];
    }
}
