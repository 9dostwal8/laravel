<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeReportsResource\Pages;
use App\Filament\Resources\OfficeReportsResource\RelationManagers;
use App\Models\OfficeReport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeReportsResource extends Resource
{
    protected static ?string $model = OfficeReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

//    public static function shouldRegisterNavigation(): bool
//    {
//        return auth()->user()->email == 'patris.support@gmail.com';
//    }

    public static function getModelLabel(): string
    {
        return trans('resources.office_report');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.office_reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.archives');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ldo_fields('office-report'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ldo_columns('office-report'))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListOfficeReports::route('/'),
            'create' => Pages\CreateOfficeReports::route('/create'),
            'edit' => Pages\EditOfficeReports::route('/{record}/edit'),
        ];
    }
}
