<?php

namespace App\Filament\Resources\ActivityAreaResource\RelationManagers;

use App\Filament\Resources\ActivityTypeResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'activityTypes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('resources.activity-type.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.activity-type.single');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(ActivityTypeResource::form($form)->getComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            //->recordTitleAttribute('name')
            ->columns(ActivityTypeResource::table($table)->getVisibleColumns())
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
