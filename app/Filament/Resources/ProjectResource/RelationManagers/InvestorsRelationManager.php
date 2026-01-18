<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\InvestorProjectResource;
use App\Filament\Resources\InvestorResource;
use App\Models\InvestorProject;
use App\Policies\ProjectPolicy;
use App\Traits\LangSwitcher;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvestorsRelationManager extends RelationManager
{
    use LangSwitcher;

    protected static string $relationship = 'investors';

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
        return trans('resources.investor.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return trans('resources.investor.single');
    }

    public static function getPluralModelLabel(): ?string
    {
        return trans('resources.investor.single');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(InvestorResource::form($form)->getComponents(true));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute(self::getModelName())
            ->columns(array_merge(InvestorResource::table($table)->getColumns(), [
                Tables\Columns\TextColumn::make('project_percentage')
                    ->prefix('%')
                    ->label(trans('resources.investor.project_percentage'))
            ]))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('project_percentage')
                            ->label(trans('resources.progress.progress_percentage'))
                            ->default(0)
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->helperText(function () {
                                $currentTotal = $this->ownerRecord->investors->sum('pivot.project_percentage');
                                $remaining = 100 - $currentTotal;
                                return "Current total: " . $currentTotal . "% | Remaining: " . $remaining . "%";
                            })
                    ])
                    ->before(function (Tables\Actions\Action $action, $data) {
                        // Check if investor is already attached (the selected record key might be different)
                        $selectedInvestorId = $data['recordId'] ?? $data['record'] ?? null;
                        if ($selectedInvestorId && $this->ownerRecord->investors->contains('id', $selectedInvestorId)) {
                            Notification::make()
                                ->danger()
                                ->persistent()
                                ->title('This investor is already attached to this project')
                                ->send();
                            $action->cancel();
                            return;
                        }

                        // Check percentage validation
                        $currentTotal = $this->ownerRecord->investors->sum('pivot.project_percentage');
                        $newPercentage = $data['project_percentage'];
                        $totalAfterAdd = $currentTotal + $newPercentage;

                        if ($totalAfterAdd > 100) {
                            Notification::make()
                                ->danger()
                                ->persistent()
                                ->title(trans('resources.project_process_must_not_greater_100'))
                                ->send();
                            $action->cancel();
                        }
                    })
                    ->recordTitle(fn($record) => self::getTranslation($record->name)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\EditAction::make('custom-edit')
                    ->label(__('filament-actions::attach.single.label'))
                    ->form([
                        TextInput::make('project_percentage')
                            ->label(trans('resources.progress.progress_percentage'))
                            ->default(0)
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                    ])
                    ->fillForm(function ($record): array {
                        return [
                            'project_percentage' => $record->pivot->project_percentage ?? 0,
                        ];
                    })
                    ->before(function (Tables\Actions\Action $action, $data, $record) {
                        $sum = $data['project_percentage'];
                        foreach ($this->ownerRecord->investors as $investor) {
                            // Skip the current investor being edited to avoid double counting
                            if ($investor->id !== $record->id) {
                                $sum += $investor->pivot->project_percentage;
                            }
                        }

                        if ($sum > 100) {
                            Notification::make()
                                ->danger()
                                ->persistent()
                                ->title(trans('resources.project_process_must_not_greater_100'))
                                ->send();
                            $action->cancel();
                        }
                    })
                    ->using(function ($record, array $data): Model {
                        $this->ownerRecord->investors()->updateExistingPivot($record->id, [
                            'project_percentage' => $data['project_percentage']
                        ]);
                        return $record;
                    }),
                Tables\Actions\Action::make('activities')
                    ->label(trans('resources.activities'))
                    ->link()
                    ->openUrlInNewTab() 
                    ->url(function ($record) {
                        $investorProject = InvestorProject::where('investor_id', $record->id)
                        ->where('project_id', $this->ownerRecord->id)
                        ->first();

                        if (!$investorProject) {
                            return null;
                        }
                        
                        
                        return InvestorProjectResource::getUrl('activities', ['record' => $investorProject]);
                    }),
                Tables\Actions\DetachAction::make(),

            ]);
    }
}
