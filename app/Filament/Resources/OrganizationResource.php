<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Filament\Resources\OrganizationResource\RelationManagers;
use App\Models\Organization;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OrganizationResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.organization.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.organization.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.organization.name'))
                    ->schema(TranslatableField::make()),
                TextInput::make('slug')
                    ->label(trans('resources.organization.slug'))
                    ->hint(trans('resources.organization.slug-hint'))
                    ->mask('aaa')
                    ->dehydrateStateUsing(fn (string $state): string => strtolower($state))
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(3),
                Select::make('licensing_authorities')
                    ->relationship('licensingAuthorities', 'name')
                    ->label(trans('resources.licensing-authorities.plural'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                    ->createOptionForm(TranslatableField::make())
                    ->multiple()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('resources.organization.name'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                TextColumn::make('slug')
                    ->label(trans('resources.organization.slug'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(trans('resources.organization.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.organization.updated_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($action) {

                            foreach ($action->getRecords() as $record) {
                                if ($record->projects()->count() > 0) {
                                    Notification::make()
                                        ->danger()
                                        ->persistent()
                                        ->title(trans('resources.organization_belongs_projects_cant_be_deleted'))
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'view' => Pages\ViewOrganization::route('/{record}'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
