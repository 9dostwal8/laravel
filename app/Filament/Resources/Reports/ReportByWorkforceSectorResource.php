<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByWorkforceSectorResource\Pages;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;


class ReportByWorkforceSectorResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static string $tableContent = 'reports.report-by-workforce-sector';

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
            'index' => Pages\ListWorkforceSector::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::customReportQuery()
            ->with(['projectVariants.activityArea']);
    }
} 