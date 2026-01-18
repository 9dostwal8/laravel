<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatusResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.statuses.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.statuses.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make(trans('resources.organization.name'))
                    ->schema(TranslatableField::make()),

                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('slug')
                        ->label(trans('resources.organization.slug'))
                        ->required(),

                    Forms\Components\Select::make('project_type')
                        ->label(trans('resources.project_type'))
                        ->options([
                            1 => trans('project_status_enum.in_progress'),
                            5 => trans('project_status_enum.canceled'),
                            7 => trans('project_status_enum.licensed'),
                            8 => trans('project_status_enum.trading'),
                        ]),

                    Forms\Components\Select::make('type')
                        ->label(trans('resources.alert.type'))
                        ->required()
                        ->options(function () {
                            $resources = [];

                            foreach (Filament::getResources() as $resource) {
                                $resources[$resource] = $resource::getModelLabel();
                            }

                            return $resources;
                        }),

                    Forms\Components\TextInput::make('color')
                        ->label(trans('resources.color'))
                        ->type('color'),
                ])->columns(4)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn ($record) => self::getTranslation($record?->name))
                    ->label(trans('resources.organization.name'))
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label(trans('resources.organization.slug'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(trans('resources.alert.type'))
                    ->formatStateUsing(function ($record) {
                        return $record->type::getModelLabel();
                    })
                    ->searchable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(trans('resources.color'))
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
                                        ->title(trans('resources.status_belongs_projects_cant_be_deleted'))
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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
