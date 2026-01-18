<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicensingAuthorityResource\Pages;
use App\Filament\Resources\LicensingAuthorityResource\RelationManagers;
use App\Models\LicensingAuthority;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicensingAuthorityResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = LicensingAuthority::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.licensing-authorities.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.licensing-authorities.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.organization.name'))
                    ->schema(TranslatableField::make()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('resources.organization.name'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
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
                                if ($record->organizations()->count() > 0) {
                                    Notification::make()
                                        ->danger()
                                        ->persistent()
                                        ->title(trans('resources.licensing_authority_belongs_organizations_cant_be_deleted'))
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
            'index' => Pages\ListLicensingAuthorities::route('/'),
            'create' => Pages\CreateLicensingAuthority::route('/create'),
            'edit' => Pages\EditLicensingAuthority::route('/{record}/edit'),
        ];
    }
}
