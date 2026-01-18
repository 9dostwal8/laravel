<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Filament\Resources\InspectionResource\RelationManagers\InspectorsRelationManager;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-viewfinder-circle';

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-projects');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.inspection.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.inspection.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(trans('resources.inspection.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('started_activity_at')
                    ->label(trans('resources.inspection.started_activity_at'))
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('ends_activity_at')
                    ->label(trans('resources.inspection.ends_activity_at'))
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->after('started_activity_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.inspection.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_activity_at')
                    ->label(trans('resources.inspection.started_activity_at'))
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_activity_at')
                    ->label(trans('resources.inspection.ends_activity_at'))
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.inspection.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.inspection.updated_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            InspectorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
        ];
    }
}
