<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByProjectUnitCityResidencyResource\Pages;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByProjectUnitCityResidencyResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static string $tableContent = 'reports.report-by-project-unit-city-residency';

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
            'index' => Pages\ListProjectUnitCityResidency::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = static::customReportQuery();
        
        return $query
            ->whereHas('projectVariants', function (Builder $query) {
                $query->where('activity_area_id', 1);
            })
           ->whereNotNull('state_id')
           ->whereHas('state') // Ensure state relationship exists
            ->with([
                'state',
                'amenities' => function ($query) {
                    $query->whereIn('amenity_id', [13, 132, 2]); // House, Apartment, Villa
                },
                'projectVariants'
            ]);
    }
} 