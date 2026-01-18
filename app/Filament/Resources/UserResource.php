<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Models\ActivityArea;
use App\Models\User;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.settings');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.user.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.user.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(trans('resources.user.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(trans('resources.user.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label(trans('resources.user.password'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->forceSearchCaseInsensitive()
                    ->relationship('roles', 'name')
                    ->label(trans('resources.user.roles'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('organization_id')
                    ->label(trans('resources.organization.single'))
                    ->relationship('organization', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                    ->createOptionForm(OrganizationResource::form($form)->getComponents())
                    ->hiddenOn(UsersRelationManager::class)
                    ->searchable(['name->ckb', 'name->en'])
                    ->preload()
                    ->required(),
                Forms\Components\Toggle::make('activated')
                    ->label(trans('resources.user.activated'))
                    ->required()
                    ->default(true),

                Select::make('can_see')
                    ->label(trans('resources.can_see_all_projects'))
                    ->default(0)
                    ->required()
                    ->options([
                        0 => trans('resources.no'),
                        1 => trans('resources.yes'),
                    ]),

                Select::make('can_edit')
                    ->label(trans('resources.can_edit_all_projects'))
                    ->default(0)
                    ->required()
                    ->options([
                        0 => trans('resources.no'),
                        1 => trans('resources.yes'),
                    ]),

                Select::make('can_insert_progress')
                    ->label(trans('resources.can_insert_progress'))
                    ->default(0)
                    ->required()
                    ->options([
                        0 => trans('resources.no'),
                        1 => trans('resources.yes'),
                    ]),

                Select::make('activity_area_limit')
                    ->label( trans('resources.activity-area.single'))
                    ->multiple()
                    ->options(function () {
                        $final = [];
                        $items = ActivityArea::pluck('name', 'id')->toArray();
                        foreach ($items as $key => $item) {
                            $final[$key] = self::getTranslation($item);
                        }

                        return $final;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('resources.user.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(trans('resources.user.email'))
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('activated')
                    ->label(trans('resources.user.activated'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.user.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.user.updated_at'))
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
                Tables\Actions\Action::make('reset_2fa')
                    ->visible(auth()->user()->isAdmin())
                    ->label(trans('resources.reset_2fa'))
                    ->action(function (User $record) {
                        $record->two_factor_secret = null;
                        $record->two_factor_recovery_codes = null;
                        $record->two_factor_confirmed_at = null;
                        $record->save();
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
