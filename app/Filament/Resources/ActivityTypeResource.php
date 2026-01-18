<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityAreaResource\RelationManagers\ActivityTypesRelationManager;
use App\Filament\Resources\ActivityTypeResource\Pages;
use App\Models\ActivityType;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityTypeResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = ActivityType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.activity-type.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.activity-type.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.activity-type.name'))
                    ->schema(TranslatableField::make()),
                Forms\Components\Select::make('activity_area_id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                    ->relationship('activityArea', 'name')
                    ->label(trans('resources.activity-area.single'))
                    ->hiddenOn(ActivityTypesRelationManager::class)
                    ->searchable(['name->ckb', 'name->en'])
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('activityArea.name')
                    ->label(trans('resources.activity-area.single'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record?->activityArea->name))
                    ->hiddenOn(ActivityTypesRelationManager::class)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.activity-type.name'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.activity-type.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.activity-type.updated_at'))
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($action) {

                            foreach ($action->getRecords() as $record) {
                                if ($record->projects()->count()) {
                                    Notification::make()
                                        ->danger()
                                        ->persistent()
                                        ->title(trans('resources.activity_type_belongs_sector_cant_be_deleted'))
                                        ->send();
                                    $action->cancel();
                                }
                            }


                        }),
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
            'index' => Pages\ListActivityTypes::route('/'),
            'create' => Pages\CreateActivityType::route('/create'),
            'edit' => Pages\EditActivityType::route('/{record}/edit'),
        ];
    }
}
