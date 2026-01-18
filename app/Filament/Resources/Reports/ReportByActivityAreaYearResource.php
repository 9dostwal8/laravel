<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByActivityAreaYearResource\Pages\ListReportByActivityAreaYear;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportByActivityAreaYearResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.report-by-activity-area-year';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportByActivityAreaYear::route('/'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return static::customReportQuery()
            ->with('projectVariants.activityArea');
    }
}
