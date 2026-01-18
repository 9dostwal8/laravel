<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\NoteResource;
use App\Policies\ProjectPolicy;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotesRelationManager extends RelationManager
{
    use LangSwitcher;

    protected static string $relationship = 'notes';

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
        return trans('resources.note.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.note.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.note.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('details')
                    ->label(trans('resources.note.details'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(trans('resources.note.description'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('project.project_name')
                    ->label(trans('resources.project.single'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record?->project->project_name))
                    ->sortable(),
                Tables\Columns\TextColumn::make('details')
                    ->label(trans('resources.note.details')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('resources.note.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.note.updated_at'))
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
                    ->url(fn ($record) => NoteResource::getUrl('activities', ['record' => $record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
