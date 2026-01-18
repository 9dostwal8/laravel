<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProgressResource;
use App\Policies\ProjectPolicy;
use App\Rules\ProgressDate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'progresses';

    protected function can(string $action, ?Model $record = null): bool
    {
        $check = auth()->user()->can_insert_progress || (new ProjectPolicy())->update(auth()->user(), $this->getOwnerRecord());

        return $check;
    }

    protected function canView(Model $record): bool
    {
        return (new ProjectPolicy())->view(auth()->user(), $this->getOwnerRecord());
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('resources.progress.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.progress.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.progress.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('inspection_id')
                    ->label(trans('resources.inspection.single'))
                    ->forceSearchCaseInsensitive()
                    ->relationship('inspection', 'name')
                  //  ->searchable()
                    ->required(),
                TextInput::make('progress_percentage')
                    ->label(trans('resources.progress.percentage'))
                    // ->options(function () {
                    //     $options = range(0, 100);

                    //     return array_combine($options, $options);
                    // })
                    ->required(),
                Textarea::make('description')
                    ->label(trans('resources.progress.description')),

                DatePicker::make('visited_at')
                    ->label(trans('resources.progress.visited_at'))
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->rule(new ProgressDate($this->getOwnerRecord()->id))
                    ->required(),

                FileUpload::make('doc')
                    ->label(trans('resources.document.attachment'))
                    ->openable()
                    ->safeDefaults()
                    ->maxSize(5122)
                    ->downloadable()
                    ->required()
                    ->visibility('private')
                    ->directory('projects-progress-docs'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('inspection.name')
                    ->label(trans('resources.inspection.single'))
                    ->sortable(),
                TextColumn::make('progress_percentage')
                    ->label(trans('resources.progress.percentage'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('visited_at')
                    ->label(trans('resources.progress.visited_at'))
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('resources.progress.created_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('resources.progress.updated_at'))
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (Tables\Actions\Action $action, $data) {
                        $sum = $data['progress_percentage'];
                        foreach ($this->ownerRecord->progresses as $progress) {
                            $sum += $progress->progress_percentage;
                        }

                        if ($sum > 100) {
                            Notification::make()
                                ->danger()
                                ->persistent()
                                ->title(trans('resources.project_process_must_not_greater_100'))
                                ->send();
                            $action->cancel();
                        }

                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('activities')
                    ->label(trans('resources.activities'))
                    ->link()
                    ->openUrlInNewTab()
                    ->url(fn ($record) => ProgressResource::getUrl('activities', ['record' => $record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
