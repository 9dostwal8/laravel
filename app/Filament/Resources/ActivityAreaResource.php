<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityAreaResource\Pages;
use App\Filament\Resources\ActivityAreaResource\RelationManagers\ActivityTypesRelationManager;
use App\Models\ActivityArea;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityAreaResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = ActivityArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.activity-area.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.activity-area.plural');
    }

    public static function form(Form $form): Form
    {
        $text = 'ئاگاداری گرنگ :  بۆ سێکتەری پیشەسازی پێویستە
        ناوی ئینگلیزی یەکسان بێت بە (Industry) وە
        بۆ سێکتەری نیشتەجی بوون یەکسان بێت بە (Housing).
        بە هیچ شێوازێک لە ناوی ئینگلیزی ئەم دوو سێکتەرە گۆڕانکاری مەکەن .';

        return $form
            ->schema([
                Fieldset::make(trans('resources.activity-area.name'))
                    ->schema(TranslatableField::make(enHelperText: $text)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn ($record) => self::getTranslation($record->name))
                    ->label(trans('resources.activity-area.name'))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.activity-area.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.activity-area.updated_at'))
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
                                        ->title(trans('resources.sector_belongs_projects_cant_be_deleted'))
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
            ActivityTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityAreas::route('/'),
            'create' => Pages\CreateActivityArea::route('/create'),
            'edit' => Pages\EditActivityArea::route('/{record}/edit'),
        ];
    }
}
