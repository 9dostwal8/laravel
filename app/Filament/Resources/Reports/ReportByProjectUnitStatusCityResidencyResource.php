<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByProjectUnitStatusCityResidencyResource\Pages;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByProjectUnitStatusCityResidencyResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $tableContent = 'reports.report-by-project-unit-status-city-residency';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportByProjectUnitStatusCityResidency::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
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
       // return parent::getEloquentQuery()
        return static::customReportQuery()
            ->whereHas('projectVariants', function (Builder $query) {
                $query->where('activity_area_id', 1);
            })
            ->with(['state'])
            ->withCount(['amenities as completed_units' => function (Builder $query) {
                $query->where('status', 2);
            }])
            ->withCount(['amenities as incomplete_units' => function (Builder $query) {
                $query->where('status', 1);
            }])
            ->withCount(['amenities as stopped_units' => function (Builder $query) {
                $query->where('status', 3);
            }])
            ->withCount(['amenities as not_started_units' => function (Builder $query) {
                $query->where('status', 4);
            }])
            ->withCount(['amenities as high_price_units' => function (Builder $query) {
                $query->where('ranking', 3);
            }])
            ->withCount(['amenities as medium_price_units' => function (Builder $query) {
                $query->where('ranking', 2);
            }])
            ->withCount(['amenities as low_price_units' => function (Builder $query) {
                $query->where('ranking', 1);
            }])
            ->withCount(['amenities as total_units' => function (Builder $query) {
                $query->whereIn('status', [1, 2, 3, 4]);
            }]);
    }
} 