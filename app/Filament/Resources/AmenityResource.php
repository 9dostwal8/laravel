<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AmenityResource\Pages;
use App\Filament\Resources\AmenityResource\RelationManagers;
use App\Models\Amenity;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AmenityResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Amenity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.amenity.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.amenity.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.amenity.name'))
                    ->schema(TranslatableField::make()),

                Section::make([
                    Select::make('activity_area_id')
                        ->relationship('activityArea', 'name')
                        ->label(trans('resources.activity-area.single'))
                        ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                        ->searchable(['name->ckb', 'name->en'])
                        ->preload()
                        ->required(),
                    Toggle::make('has_production_rate')
                        ->label(trans('resources.amenity.has_production_rate'))
                        ->required(),
                    Toggle::make('ranking')
                        ->label(trans('resources.has_ranking'))
                        ->required(),
                ])->columns(3)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                TextColumn::make('activityArea.name')
                    ->formatStateUsing(fn ($record) => self::getTranslation($record?->activityArea->name))
                    ->url(fn($record) => ActivityAreaResource::getUrl('edit', ['record' => $record]))
                    ->label(trans('resources.activity-area.single'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('resources.amenity.name'))
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'LIKE', "%{$search}%")
                            ->orWhere('name->ar', 'LIKE', "%{$search}%")
                            ->orWhere('name->en', 'LIKE', "%{$search}%");
                    }),
                Tables\Columns\IconColumn::make('has_production_rate')
                    ->label(trans('resources.amenity.has_production_rate'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('resources.amenity.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.amenity.updated_at'))
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
                                        ->title(trans('resources.amenity_type_belongs_sector_cant_be_deleted'))
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
            'index' => Pages\ListAmenities::route('/'),
            'create' => Pages\CreateAmenity::route('/create'),
            'edit' => Pages\EditAmenity::route('/{record}/edit'),
        ];
    }
}
