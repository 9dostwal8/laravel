<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommandResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\CommandsRelationManager;
use App\Models\Command;
use App\Traits\LangSwitcher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommandResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Command::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-projects');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.command.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.command.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('project_id')
                    ->relationship('project', 'project_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->project_name))
                    ->hiddenOn(CommandsRelationManager::class)
                    ->label(trans('resources.project.single'))
                    ->searchable()
                    ->preload(),
                TextInput::make('number')
                    ->label(trans('resources.command.number'))
                    ->required()
                    ->numeric(),
                TextInput::make('subject')
                    ->label(trans('resources.command.subject'))
                    ->required()
                    ->maxLength(255),
                DatePicker::make('submitted_at')
                    ->label(trans('resources.command.submitted_at'))
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->required(),
                FileUpload::make('attachment')
                    ->label(trans('resources.command.attachment'))
                    ->required()
                    ->openable()
                    ->maxSize(5122)
                    ->downloadable()
                    ->safeDefaults()
                    ->visibility('private')
                    ->directory('command-attachments'),
                Hidden::make('organization_id')
                    ->default(auth()->user()->organization_id)
                    ->required()
                    ->label(trans('resources.organization.single')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                TextColumn::make('project.project_name')
                    ->label(trans('resources.project.single'))
                    ->formatStateUsing(fn ($record) => self::getTranslation($record?->project->project_name))
                    ->hiddenOn(CommandsRelationManager::class)
                    ->sortable(),
                TextColumn::make('number')
                    ->label(trans('resources.command.number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(trans('resources.command.subject'))
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->label(trans('resources.command.submitted_at'))
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('resources.command.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.command.updated_at'))
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

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->isAdmin()) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()
            ->where('organization_id', auth()->user()->organization_id); // TODO: Change the autogenerated stub
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommands::route('/'),
          //  'view' => Pages\ViewCommand::route('/{record}'),
            'create' => Pages\CreateCommand::route('/create'),
            'edit' => Pages\EditCommand::route('/{record}/edit'),
            'activities' => Pages\ListCommandActivities::route('/{record}/activities'),
        ];
    }
}
