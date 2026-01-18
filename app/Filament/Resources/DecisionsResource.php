<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DecisionsResource\Pages;
use App\Filament\Resources\DecisionsResource\RelationManagers;
use App\Models\Decision;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DecisionsResource extends Resource
{
    protected static ?string $model = Decision::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

//    public static function shouldRegisterNavigation(): bool
//    {
//        return auth()->user()->email == 'patris.support@gmail.com';
//    }

    public static function getModelLabel(): string
    {
        return trans('resources.decision_&_guideline');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.decisions_&_guidelines');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.archives');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ldo_fields('decision'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ldo_columns('decision'))
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
            'index' => Pages\ListDecisions::route('/'),
            'create' => Pages\CreateDecisions::route('/create'),
            'edit' => Pages\EditDecisions::route('/{record}/edit'),
        ];
    }
}
