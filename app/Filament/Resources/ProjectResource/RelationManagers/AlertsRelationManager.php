<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\AlertTypeEnum;
use App\Filament\Resources\AlertResource;
use App\Policies\ProjectPolicy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AlertsRelationManager extends RelationManager
{
    protected static string $relationship = 'alerts';

    protected function can(string $action, ?Model $record = null): bool
    {
        return (new ProjectPolicy())->update(auth()->user(), $this->getOwnerRecord());
    }

    protected function canView(Model $record): bool
    {
        return (new ProjectPolicy())->view(auth()->user(), $this->getOwnerRecord());
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('resources.alert.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.alert.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.alert.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('inspection_id')
                    ->relationship('inspection', 'name')
                    ->forceSearchCaseInsensitive()
                    ->label(trans('resources.inspection.single'))
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->label(trans('resources.alert.subject'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label(trans('resources.alert.type'))
                    ->options(AlertTypeEnum::class)
                    ->required(),
                Forms\Components\TextInput::make('visit_deadline')
                    ->label(trans('resources.alert.visit_deadline'))
                    ->required()
                    ->maxLength(200)
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->label(trans('resources.alert.description'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('inspection.name')
                    ->label(trans('resources.inspection.single'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label(trans('resources.alert.subject'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(trans('resources.alert.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('visit_deadline')
                    ->label(trans('resources.alert.visit_deadline'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.alert.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.alert.updated_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('activities')
                    ->label(trans('resources.activities'))
                    ->link()
                    ->openUrlInNewTab()
                    ->url(fn ($record) => AlertResource::getUrl('activities', ['record' => $record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
