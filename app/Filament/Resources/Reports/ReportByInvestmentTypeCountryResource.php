<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByInvestmentTypeCountryResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.report-by-investment-type-country';

    public static function getPluralLabel(): ?string
    {
        return trans('resources.the_utilization_based_on_the.type_of_user_country_number_of_projects_capital_area');
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
            ->with([
                'investors.country', // Load investors and their countries
                'originalCountries', // Load project countries
                'projectVariants' // Load project variants for capital calculation
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Reports\ReportByInvestmentTypeCountryResource\Pages\ListReportByInvestmentTypeCountry::route('/'),
        ];
    }
}
