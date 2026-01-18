<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentBySectorResource\Pages;
use App\Models\ActivityArea;
use App\Traits\LangSwitcher;
use App\Traits\ReportsQuery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvestmentBySectorResource extends Resource
{
    use ReportsQuery, LangSwitcher;

    protected static ?string $model = ActivityArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static $tableContent = 'reports.investment-by-sector';

    protected static bool $shouldRegisterNavigation = false;


    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

        ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestmentBySectors::route('/'),
        ];
    }
}
