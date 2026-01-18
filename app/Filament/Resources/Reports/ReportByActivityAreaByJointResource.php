<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ReportByActivityAreaByJointResource\Pages;
use App\Filament\Resources\Reports\ReportByActivityAreaByJointResource\RelationManagers;
use App\Models\Project;
use App\Models\Reports\ReportByActivityAreaByJoint;
use App\Traits\ReportsQuery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportByActivityAreaByJointResource extends Resource
{
    use ReportsQuery;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.report-by-activity-area-by-joint';

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
            'index' => Pages\ListReportByActivityAreaByJoints::route('/')
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::customReportQuery()
            ->with(['projectVariants.activityArea']);
    }
}
