<?php

namespace App\Filament\Resources\InspectionResource\RelationManagers;

use App\Enums\InspectorTypeEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InspectorsRelationManager extends RelationManager
{
    protected static string $relationship = 'inspectors';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('resources.inspector.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.inspector.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.inspector.single');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(trans('resources.inspector.name'))
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label(trans('resources.inspector.type'))
                    ->options(InspectorTypeEnum::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.inspector.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(trans('resources.inspector.type'))
                    ->badge()
                    ->color(fn (InspectorTypeEnum $state): string => $state->getColor())
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.inspector.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.inspector.updated_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->forceSearchCaseInsensitive()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
