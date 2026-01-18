<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\LetterRecipientTypeEnum;
use App\Enums\LetterTypeEnum;
use App\Models\Letter;
use App\Policies\ProjectPolicy;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LettersRelationManager extends RelationManager
{
    protected static string $relationship = 'letters';

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
        return trans('resources.letter.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.letter.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.letter.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('letter_type')
                    ->label(trans('resources.letter.letter_type'))
                    ->options(LetterTypeEnum::class)
                    ->required(),
                Select::make('recipient_type')
                    ->label(trans('resources.letter.recipient_type'))
                    ->options(LetterRecipientTypeEnum::class)
                    ->required(),
                TextInput::make('subject')
                    ->label(trans('resources.letter.subject'))
                    ->required()
                    ->maxLength(255),
                FileUpload::make('attachment')
                    ->label(trans('resources.letter.attachment'))
                    ->required()
                    ->safeDefaults()
                    ->downloadable()
                    ->maxSize(5122)
                    ->openable()
                    ->visibility('private')
                    ->directory('letter-attachments'),
                TextInput::make('number')
                    ->label(trans('resources.letter.number'))
                    ->required()
                    ->maxLength(255),
                DatePicker::make('submitted_at')
                    ->label(trans('resources.letter.submitted_at'))
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('subject')
                    ->label(trans('resources.letter.subject'))
                    ->searchable(),
                TextColumn::make('letter_type')
                    ->label(trans('resources.letter.letter_type'))
                    ->badge(),
                TextColumn::make('recipient_type')
                    ->label(trans('resources.letter.recipient_type'))
                    ->badge(),
                TextColumn::make('number')
                    ->label(trans('resources.letter.number'))
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->label(trans('resources.letter.submitted_at'))
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('resources.letter.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.letter.updated_at'))
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
                    ->url(fn (Letter $record) => route('filament.admin.resources.letters.activities', $record))
                    ->icon('heroicon-o-clock'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
