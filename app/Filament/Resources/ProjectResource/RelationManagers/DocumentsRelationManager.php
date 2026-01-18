<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\DocumentTypeEnum;
use App\Models\Document;
use App\Policies\ProjectPolicy;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

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
        return trans('resources.document.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.document.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.document.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label(trans('resources.document.type'))
                    ->options(DocumentTypeEnum::class)
                    ->required(),
                FileUpload::make('attachment')
                    ->label(trans('resources.document.attachment'))
                    ->required()
                    ->safeDefaults()
                    ->openable()
                    ->maxSize(5122)
                    ->downloadable()
                    ->visibility('private')
                    ->directory('document-attachments'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label(trans('resources.document.type'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(trans('resources.document.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.document.updated_at'))
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
                    ->url(fn (Document $record) => route('filament.admin.resources.documents.activities', $record))
                    ->icon('heroicon-o-clock'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
