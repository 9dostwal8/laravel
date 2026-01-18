<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LawResource\Pages;
use App\Filament\Resources\LawResource\RelationManagers;
use App\Models\Law;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LawResource extends Resource
{
    protected static ?string $model = Law::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @param int|null $navigationSort
     */

    protected static ?int $navigationSort = 1;

//    public static function shouldRegisterNavigation(): bool
//    {
//        return auth()->user()->email == 'patris.support@gmail.com';
//    }

    public static function getModelLabel(): string
    {
        return trans('resources.law');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.laws');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.archives');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ldo_fields('law'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ldo_columns('law'))
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
            'index' => Pages\ListLaws::route('/'),
            'create' => Pages\CreateLaw::route('/create'),
            'edit' => Pages\EditLaw::route('/{record}/edit'),
        ];
    }
}
