<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StateResource\Pages;
use App\Models\State;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StateResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = State::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-places');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.state.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.state.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.state.name'))
                    ->schema(TranslatableField::make()),
                Forms\Components\Select::make('country_id')
                    ->label(trans('resources.country.single'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                    ->relationship('country', 'name')
                    ->preload()
                    ->searchable(['name->ckb', 'name->en'])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('country.name')
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->country->name))
                    ->label(trans('resources.country.single'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.state.name'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.state.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.state.updated_at'))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStates::route('/'),
            'create' => Pages\CreateState::route('/create'),
            'edit' => Pages\EditState::route('/{record}/edit'),
        ];
    }
}
