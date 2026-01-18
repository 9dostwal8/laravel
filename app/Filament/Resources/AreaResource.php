<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
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

class AreaResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return trans('resources.area.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.area.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-places');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.area.name'))
                    ->schema(TranslatableField::make()),
                Forms\Components\Select::make('department_id')
                    ->label(trans('resources.department.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('department.name')
                    ->formatStateUsing(fn($record) => self::getTranslation($record?->department->name))
                    ->label(trans('resources.department.single'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.area.name'))
                    ->formatStateUsing(fn($record) => self::getTranslation($record->name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.area.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.area.updated_at'))
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
                                if ($record->projects()->count() >= 1) {
                                    Notification::make()
                                        ->danger()
                                        ->persistent()
                                        ->title(trans('resources.area_belongs_projects_cant_be_deleted'))
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
