<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Traits\LangSwitcher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use PepperFM\FilamentJson\Columns\JsonColumn;

class ActivityLogResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.basic-information');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.activity');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.activities');
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(trans('resources.event'))
                    ->options([
                        'created' => trans('resources.created'),
                        'updated' => trans('resources.updated'),
                        'deleted' => trans('resources.deleted'),
                    ]),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(trans('resources.project_name'))
                    ->searchable()
                    ->options(function () {
                        // Load last 10 projects as default options
                        return \App\Models\Project::query()
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => self::getTranslation($project->project_name) . ' (ID: ' . $project->id . ')'
                            ])
                            ->toArray();
                    })
                    ->getSearchResultsUsing(fn (string $search): array => 
                        \App\Models\Project::query()
                            ->where(function ($query) use ($search) {
                                $query->where('project_name->en', 'like', "%{$search}%")
                                    ->orWhere('project_name->ckb', 'like', "%{$search}%")
                                    ->orWhere('project_name->ar', 'like', "%{$search}%");
                            })
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => self::getTranslation($project->project_name) . ' (ID: ' . $project->id . ')'
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => 
                        \App\Models\Project::find($value)?->project_name 
                            ? self::getTranslation(\App\Models\Project::find($value)->project_name) . ' (ID: ' . $value . ')'
                            : null
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $projectId): Builder => $query->where(function ($q) use ($projectId) {
                                    // Search in direct subject projects
                                    $q->where('subject_type', 'App\\Models\\Project')
                                        ->where('subject_id', $projectId)
                                    // Also search in parent projects for related activities
                                    ->orWhere('parent_subject_type', 'App\\Models\\Project')
                                        ->where('parent_subject_id', $projectId);
                                })
                            );
                    }),
                Tables\Filters\SelectFilter::make('project_by_id')
                    ->label(trans('resources.project_id'))
                    ->searchable()
                    ->options(function () {
                        // Load last 10 projects as default options
                        return \App\Models\Project::query()
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => 'ID: ' . $project->id . ' - ' . self::getTranslation($project->project_name)
                            ])
                            ->toArray();
                    })
                    ->getSearchResultsUsing(fn (string $search): array => 
                        \App\Models\Project::query()
                            ->where('id', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => 'ID: ' . $project->id . ' - ' . self::getTranslation($project->project_name)
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => 
                        \App\Models\Project::find($value) 
                            ? 'ID: ' . $value . ' - ' . self::getTranslation(\App\Models\Project::find($value)->project_name)
                            : null
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $projectId): Builder => $query->where(function ($q) use ($projectId) {
                                    // Search in direct subject projects
                                    $q->where('subject_type', 'App\\Models\\Project')
                                        ->where('subject_id', $projectId)
                                    // Also search in parent projects for related activities
                                    ->orWhere('parent_subject_type', 'App\\Models\\Project')
                                        ->where('parent_subject_id', $projectId);
                                })
                            );
                    }),
                Tables\Filters\SelectFilter::make('project_by_license')
                    ->label(trans('resources.project.license_number'))
                    ->searchable()
                    ->options(function () {
                        // Load last 10 projects as default options
                        return \App\Models\Project::query()
                            ->whereNotNull('license_number')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => 'License: ' . $project->license_number . ' - ' . self::getTranslation($project->project_name)
                            ])
                            ->toArray();
                    })
                    ->getSearchResultsUsing(fn (string $search): array => 
                        \App\Models\Project::query()
                            ->where('license_number', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($project) => [
                                $project->id => 'License: ' . $project->license_number . ' - ' . self::getTranslation($project->project_name)
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => 
                        \App\Models\Project::find($value) 
                            ? 'License: ' . \App\Models\Project::find($value)->license_number . ' - ' . self::getTranslation(\App\Models\Project::find($value)->project_name)
                            : null
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $projectId): Builder => $query->where(function ($q) use ($projectId) {
                                    // Search in direct subject projects
                                    $q->where('subject_type', 'App\\Models\\Project')
                                        ->where('subject_id', $projectId)
                                    // Also search in parent projects for related activities
                                    ->orWhere('parent_subject_type', 'App\\Models\\Project')
                                        ->where('parent_subject_id', $projectId);
                                })
                            );
                    }),
                Tables\Filters\Filter::make('created_at_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(trans('resources.created_from'))
                            ->placeholder(trans('resources.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(trans('resources.created_until'))
                            ->placeholder(trans('resources.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Created from ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }
                        
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Created until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }
                        
                        return $indicators;
                    }),
                Tables\Filters\Filter::make('updated_at_range')
                    ->form([
                        Forms\Components\DatePicker::make('updated_from')
                            ->label(trans('resources.updated_from'))
                            ->placeholder(trans('resources.updated_from')),
                        Forms\Components\DatePicker::make('updated_until')
                            ->label(trans('resources.updated_until'))
                            ->placeholder(trans('resources.updated_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['updated_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['updated_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Updated from ' . \Carbon\Carbon::parse($data['updated_from'])->toFormattedDateString())
                                ->removeField('updated_from');
                        }
                        
                        if ($data['updated_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Updated until ' . \Carbon\Carbon::parse($data['updated_until'])->toFormattedDateString())
                                ->removeField('updated_until');
                        }
                        
                        return $indicators;
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(trans('resources.title'))
                    ->formatStateUsing(function ($record) {
                        $model = $record->subject_type;
                        $id = $record->subject_id;
                        if($model != 'App\Models\Project') {
                            $model = 'App\\Models\\Project';
                            $id = $record->parent_subject_id;
                            if(!$id || !$model) {
                                return '-';
                            }
                        }
                        $item = $model::find($id);
                        if(!$item) {
                            return '-';
                        }
                        return new HtmlString(self::getTranslation($item->project_name) . '<br/> Id: ' . $item->id . '<br/> License Number: ' . $item->license_number);
                    })
                    ,

                Tables\Columns\TextColumn::make('event')
                    ->formatStateUsing(fn ($record) => trans('resources.' . $record->event))
                    ->label(trans('resources.event')),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(trans('resources.subject_type'))
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '-';
                        }
                        
                        // Try to find the corresponding Filament resource for this model
                        $resourceClass = self::getResourceForModel($state);
                        
                        if ($resourceClass && method_exists($resourceClass, 'getModelLabel')) {
                            return $resourceClass::getModelLabel();
                        }
                        
                        // Fallback: extract model name and try to translate it
                        $modelName = class_basename($state);
                        $translationKey = 'resources.' . strtolower($modelName);
                        if($modelName == 'ProjectVariant') {
                            $translationKey = 'resources.custom_project_variant';
                        }
                        
                        return trans($translationKey) !== $translationKey 
                            ? trans($translationKey) 
                            : $modelName;
                    }),

                Tables\Columns\TextColumn::make('causer_id')
                    ->label(trans('resources.user.single'))
                    ->formatStateUsing(fn ($record) => $record->causer?->name),

                JsonColumn::make('properties')
                    ->label(trans('resources.properties')),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('resources.date'))
                    ->date('H:i:s Y-m-d')
                    ->sortable(),


            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
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
        return parent::getEloquentQuery()->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'create' => Pages\CreateActivityLog::route('/create'),
            'edit' => Pages\EditActivityLog::route('/{record}/edit'),
        ];
    }

    /**
     * Find the Filament resource class for a given model class
     */
    private static function getResourceForModel(string $modelClass): ?string
    {
        // Get all Filament resources
        $resourceClasses = collect(glob(app_path('Filament/Resources/*Resource.php')))
            ->map(function ($file) {
                $className = 'App\\Filament\\Resources\\' . basename($file, '.php');
                return class_exists($className) ? $className : null;
            })
            ->filter()
            ->values();

        // Find the resource that uses the given model
        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'getModel') && $resourceClass::getModel() === $modelClass) {
                return $resourceClass;
            }
        }

        return null;
    }

    /**
     * Get translation for name field (handles both string and array formats)
     */
    private static function getTranslation($name)
    {
        if (is_array($name)) {
            // If name is an array (multilingual), get current locale or fallback
            $locale = app()->getLocale();
            return $name[$locale] ?? $name['en'] ?? $name['ckb'] ?? array_values($name)[0] ?? '';
        }
        
        return $name ?? '';
    }
}
