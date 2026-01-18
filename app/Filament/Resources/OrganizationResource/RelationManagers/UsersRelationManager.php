<?php

namespace App\Filament\Resources\OrganizationResource\RelationManagers;

use App\Filament\Resources\UserResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $inverseRelationship = 'organization';


    /**
     * @return string|null
     */
    public static function getModelLabel(): ?string
    {
        return trans('resources.user.single');
    }

    /**
     * @return string|null
     */
    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.user.plural');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('resources.user.plural');
    }

//    public static function getPluralModelLabel(): ?string
//    {
//        return trans('resources.user.single');
//    }

//    public function getTableRecordTitle(Model $record): ?string
//    {
//        return trans('resources.user.plural');
//    }


    public function form(Form $form): Form
    {
        return $form
            ->schema(UserResource::form($form)->getComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(UserResource::table($table)->getColumns())
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
