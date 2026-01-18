<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorProjectResource\Pages;
use App\Filament\Resources\InvestorProjectResource\RelationManagers;
use App\Models\InvestorProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class InvestorProjectResource extends Resource
{
    protected static ?string $model = InvestorProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return trans('resources.custom_investor_projects');
    }

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

    public static function shouldRegisterNavigation(): bool
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
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
            'index' => Pages\ListInvestorProjects::route('/'),
            'create' => Pages\CreateInvestorProject::route('/create'),
            'edit' => Pages\EditInvestorProject::route('/{record}/edit'),
            'activities' => Pages\ListInvestorProjectActivities::route('/{record}/activities'),
        ];
    }
}
