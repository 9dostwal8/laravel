<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByServiceUnitsCityResidencyResource\Pages;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByServiceUnitsCityResidencyResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $tableContent = 'reports.service-units-city-residency';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportByServiceUnitsCityResidency::route('/'),
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
     //   return parent::getEloquentQuery()
        return static::customReportQuery()
            ->with(['state', 'projectVariants', 'amenities'])
            ->whereHas('projectVariants', function (Builder $query) {
                $query->where('activity_area_id', 1);
            })
            ->withSum(['amenities as daycare_units' => function (Builder $query) {
                $query->where('amenity_id', 17);
            }], 'counts')
            ->withSum(['amenities as kindergarten_units' => function (Builder $query) {
                $query->where('amenity_id', 18);
            }], 'counts')
            ->withSum(['amenities as school_units' => function (Builder $query) {
                $query->where('amenity_id', 10);
            }], 'counts')
            ->withSum(['amenities as health_center_units' => function (Builder $query) {
                $query->where('amenity_id', 5);
            }], 'counts')
            ->withSum(['amenities as police_station_units' => function (Builder $query) {
                $query->where('amenity_id', 526);
            }], 'counts')
            ->withSum(['amenities as water_treatment_units' => function (Builder $query) {
                $query->where('amenity_id', 496);
            }], 'counts')
            ->withSum(['amenities as total_units' => function (Builder $query) {
                $query->whereIn('amenity_id', [17, 18, 10, 5, 526, 496]);
            }], 'counts');
    }
} 