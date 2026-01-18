<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\CommandResource;
use App\Models\Command;
use App\Policies\ProjectPolicy;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommandsRelationManager extends RelationManager
{
    protected static string $relationship = 'commands';

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
        return trans('resources.command.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.command.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.command.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(CommandResource::form($form)->getComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(CommandResource::table($table)->getColumns())
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
                    ->url(fn (Command $record) => route('filament.admin.resources.commands.activities', $record))
                    ->icon('heroicon-o-clock'),
            ]);
    }
}
