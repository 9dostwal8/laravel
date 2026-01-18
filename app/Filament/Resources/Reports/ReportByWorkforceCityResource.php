<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByWorkforceCityResource\Pages;
use App\Models\Project;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ReportByWorkforceCityResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string $tableContent = 'reports.report-by-workforce-city';

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.reports');
    }

    public static function getNavigationLabel(): string
    {
        return trans('resources.workforce_city');
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
            'index' => Pages\ListWorkforceCity::route('/'),
        ];
    }
} 