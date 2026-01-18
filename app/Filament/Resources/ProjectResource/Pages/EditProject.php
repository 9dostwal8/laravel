<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\AmenityStatus;
use App\Enums\ProjectActivityAreaTypeEnum;
use App\Filament\Resources\ProjectResource;
use App\Models\Activity;
use App\Models\ActivityArea;
use App\Models\Amenity;
use App\Models\Command;
use App\Models\Project;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EditProject extends EditRecord
{
    use LangSwitcher;

    protected static string $resource = ProjectResource::class;

    protected mixed $description;

    protected mixed $commandId;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('change_name_title')
                    ->label(trans('resources.project.change_name_title'))
                    ->fillForm(fn (Project $record): array => [
                        'project_name' => $record->project_name,
                    ])
                    ->form([
                        Fieldset::make(trans('resources.project.project_name'))
                            ->schema(TranslatableField::make()),
                        Section::make(trans('resources.project.edit.title'))
                            ->description(trans('resources.project.edit.description'))
                            ->schema([
                                Select::make('command_id')
                                    ->preload()
                                    ->forceSearchCaseInsensitive()
                                    ->label(trans('resources.project.edit.command-title'))
                                    ->options(fn (Get $get): Collection => Command::query()
                                        ->where('project_id', $this->record->id)
                                        ->pluck('number', 'id'))
                                    ->unique('activity_log', 'command_id')
                                    ->searchable()
                                    ->required(),
                                Textarea::make('description')
                                    ->label(trans('resources.project.description'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->action(function (array $data, Project $project): void {
                        $this->description = Arr::pull($data, 'description');
                        $this->commandId = Arr::pull($data, 'command_id');

                        $project->update(Arr::except($data, 'description'));
                    })
                    ->after(function (Project $project) {
                        $project->activities->last()->update([
                            'description' => $this->description,
                            'command_id' => $this->commandId,
                        ]);
                    })->modalWidth(50),
                Actions\Action::make('change_activity_area')
                    ->label(trans('resources.project.change_activity_area'))
                    ->fillForm(fn (Project $record): array => [
                        'projectVariants' => $record->projectVariants,
                        'amenities' => $record->amenities,
                    ])
                    ->form([
                        Wizard::make([
                            Wizard\Step::make(trans('resources.activity-area.plural'))
                                ->schema([
                                    Repeater::make('projectVariants')
                                        ->relationship()
                                        ->hiddenLabel()
                                        ->columnSpanFull()
                                        ->schema([
                                            Select::make('activity_area_id')
                                                ->label(trans('resources.activity-area.single'))
                                                ->getOptionLabelFromRecordUsing(fn ($record) =>self::getTranslation($record->name))
                                                ->relationship('activityArea', 'name')
                                                ->searchable(['name->ckb', 'name->en'])
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('activity_type_id', null);
                                                    $set('../../amenities.*.amenity_id', null);
                                                })
                                                ->live()
                                                ->preload()
                                                ->required(),
                                            Select::make('activity_type_id')
                                                ->label(trans('resources.activity-type.single'))
                                                ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                                                ->relationship('activityType', 'name', function (Builder $query, Get $get) {
                                                    $query->where('activity_area_id', $get('activity_area_id'));
                                                })
                                                // ->createOptionForm(ActivityTypeResource::form($form)->getComponents())
                                                ->searchable(['name->ckb', 'name->en'])
                                                ->preload()
                                                ->required(),
                                            Select::make('type')
                                                ->label(trans('resources.project.activity-area-type'))
                                                ->options(ProjectActivityAreaTypeEnum::class)
                                                ->searchable()
                                                ->preload()
                                                ->required(),

                                            Section::make(trans('resources.project.finance.title'))
                                                ->description(trans('resources.project.finance.description'))
                                                ->compact()
                                                ->columns(3)
                                                ->schema([
                                                    TextInput::make('currency_rate')
                                                        ->label(trans('resources.project.finance.currency_rate'))
                                                        ->default(0)
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->numeric(),
                                                    TextInput::make('capital_dinar')
                                                        ->label(trans('resources.project.finance.capital_dinar'))
//                                                ->afterStateUpdated(function (Request $request, Get $get, Set $set, $state) {
//                                                    $updatedByCapitalDollar = array_key_exists(
//                                                        'data.capital_dollar',
//                                                        current($request->input('components'))['updates']
//                                                    );
//
//                                                    if (! $updatedByCapitalDollar) {
//                                                        $currencyRate = floatval(str_replace(',', '', $get('currency_rate')));
//                                                        $state = floatval(str_replace(',', '', $state));
//
//                                                        if ($state < 1 || $currencyRate < 1) {
//                                                            return;
//                                                        }
//
//                                                        $set('capital_dollar', number_format((float) ($state / $currencyRate), 2));
//                                                    }
//                                                })
                                                        ->numeric()
                                                        ->reactive()
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->debounce(1100)
                                                        ->required()
                                                        ->default(0),
                                                    TextInput::make('capital_dollar')
                                                        ->label(trans('resources.project.finance.capital_dollar'))
//                                                ->afterStateUpdated(function (Request $request, Get $get, Set $set, $state) {
//                                                    $updatedByCapitalDinar = array_key_exists(
//                                                        'data.capital_dinar',
//                                                        current($request->input('components'))['updates']
//                                                    );
//
//                                                    if (! $updatedByCapitalDinar) {
//                                                        $currencyRate = floatval(str_replace(',', '', $get('currency_rate')));
//                                                        $state = floatval(str_replace(',', '', $state));
//
//                                                        if ($state < 1 || $currencyRate < 1) {
//                                                            return;
//                                                        }
//
//                                                        $set('capital_dinar', number_format((float) ($state * $currencyRate), 2));
//                                                    }
//                                                })
                                                        ->numeric()
                                                        ->reactive()
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->debounce(1100)
                                                        ->required()
                                                        ->default(0),

                                                    TextInput::make('loan_fund')
                                                        ->label(trans('resources.project.finance.loan_fund'))
                                                        ->default(0)
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->visible(function(Get $get) {
                                                            $activity_area = ActivityArea::find($get('activity_area_id'));

                                                            if (isset($activity_area->name['en']) && $activity_area->name['en'] == 'Housing') {
                                                                return true;
                                                            }

                                                            return false;
                                                        })
                                                        ->currencyMask(),

                                                    TextInput::make('non_loan_fund')
                                                        ->label(trans('resources.project.finance.non_loan_fund'))
                                                        ->default(0)
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->visible(function(Get $get) {
                                                            $activity_area = ActivityArea::find($get('activity_area_id'));

                                                            if (isset($activity_area->name['en']) && $activity_area->name['en'] == 'Housing') {
                                                                return true;
                                                            }

                                                            return false;
                                                        })
                                                        ->currencyMask(),
                                                ]),
                                        ]),
                                    Section::make(trans('resources.project.edit.title'))
                                        ->description(trans('resources.project.edit.description'))
                                        ->schema([
                                            Select::make('command_id')
                                                ->preload()
                                                ->forceSearchCaseInsensitive()
                                                ->label(trans('resources.project.edit.command-title'))
                                                ->options(fn (Get $get): Collection => Command::query()
                                                    ->where('project_id', $this->record->id)
                                                    ->pluck('number', 'id'))
                                                ->searchable()
                                                ->unique('activity_log', 'command_id')
                                                ->required(),
                                            Textarea::make('description')
                                                ->label(trans('resources.project.description'))
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                ]),
                            Wizard\Step::make(trans('resources.activity-type.plural'))
                                ->schema([
                                    Repeater::make('amenities')
                                        ->relationship()
                                        ->columnSpanFull()
                                        ->label(trans('resources.amenity.plural'))
                                        ->schema([

                                            Section::make([
                                                Select::make('amenity_id')
                                                    ->label(trans('resources.amenity.single'))
                                                    ->relationship('amenity', 'name')
                                                    ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                                                    ->options(function (Get $get) {
                                                        $options = [];
                                                        $variants = $get('../../projectVariants');
                                                        foreach ($variants as $variant) {
                                                            $activity_areas = Amenity::where('activity_area_id', $variant['activity_area_id'])->get();

                                                            foreach ($activity_areas as $activity_area) {
                                                                $options[$activity_area->id] = self::getTranslation($activity_area->name);
                                                            }
                                                        }
                                                        return $options;
                                                    })
                                                    //  ->searchable(['name->ckb', 'name->en'])
                                                    ->live()
                                                    ->required(),
                                                TextInput::make('counts')
                                                    ->label(trans('resources.amenity.counts'))
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->step(1)
                                                    ->rules(['min:1', 'integer'])
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if ($state <= 0) {
                                                            $set('counts', 1);
                                                        }
                                                    }),
                                                Select::make('status')
                                                    ->label(trans('resources.amenity.status'))
                                                    ->required()
                                                    ->options(AmenityStatus::class),
                                                TextInput::make('production_rate')
                                                    ->label(trans('resources.amenity.production_rate'))
                                                    ->visible(function(Get $get) {
                                                        if (! empty($get('amenity_id'))) {
                                                            $amenity = Amenity::find($get('amenity_id'));
                                                            $name = $amenity->activityArea->name['en'] ?? null;
                                                            return isset($amenity) && $amenity->has_production_rate == 1 && $name != 'Industry';
                                                        } else {
                                                            return false;
                                                        }
                                                    })
                                                    ->default(0),


                                                Select::make('ranking')
                                                    ->label(trans('resources.ranking'))
                                                    ->required()
                                                    ->distinct()
                                                    ->visible(function(Get $get) {
                                                        if (! empty($get('amenity_id'))) {
                                                            $amenity = Amenity::find($get('amenity_id'));
                                                            $name = $amenity->activityArea->name['en'] ?? null;
                                                            $check = isset($amenity) && $amenity->ranking == 1 && $name == 'Housing';
                                                        } else {
                                                            $check = false;
                                                        }

                                                        return $check;
                                                    })
                                                    ->options([
                                                        1 => trans('resources.low'),
                                                        2 => trans('resources.mid'),
                                                        3 => trans('resources.high'),
                                                    ]),

                                                Section::make([

                                                    TextInput::make('product_type')
                                                        ->label(trans('resources.product_type')),

                                                    TextInput::make('amount')
                                                        ->label(trans('resources.amount'))
                                                        ->numeric(),

                                                    Select::make('measurement_unit')
                                                        ->label(trans('resources.measurement_unit'))
                                                        ->options([
                                                            'liter' => trans('resources.liter'),
                                                            'ton' => trans('resources.ton'),
                                                            'cubicـmeters' => trans('resources.cubicـmeters'),
                                                            'grain' => trans('resources.grain'),
                                                            'box' => trans('resources.box'),
                                                        ])

                                                ])
                                                    ->visible(function(Get $get) {
                                                        if (! empty($get('amenity_id'))) {
                                                            $amenity = Amenity::find($get('amenity_id'));
                                                            $name = $amenity->activityArea->name['en'] ?? null;
                                                            $check = isset($amenity) && $amenity->has_production_rate == 1 && $name == 'Industry';
                                                        } else {
                                                            $check = false;
                                                        }

                                                        return $check;
                                                    })
                                                    ->columns(3),

                                                Textarea::make('description')
                                                    ->columnSpanFull()
                                                    ->label(trans('resources.amenity.description')),

                                            ])->columns(4),

                                        ]),

                                    Textarea::make('amenity_description')
                                        ->label(trans('resources.project.amenity_description'))
                                        ->columnSpanFull(),
                                ])
                        ])->skippable(),
                    ])
                    ->action(function (array $data): void {
                        $this->description = Arr::pull($data, 'description');
                        $this->commandId = Arr::pull($data, 'command_id');
                    })
                    ->after(function (Project $project) {
                        Activity::whereIn('subject_id', $project->projectVariants()->pluck('id'))
                            ->latest()
                            ->update([
                                'description' => $this->description,
                                'command_id' => $this->commandId,
                            ]);
                    }),
                Actions\Action::make('change_execution_time')
                    ->label(trans('resources.project.change_execution_time'))
                    ->fillForm(fn (Project $record): array => [
                        'started_at' => $record->started_at,
                        'estimated_project_end_date' => $record->estimated_project_end_date,
                        'actual_project_end_date' => $record->actual_project_end_date,
                        'execution_time_years' => $record->execution_time_years,
                        'execution_time_months' => $record->execution_time_months,
                    ])
                    ->form([
                        DatePicker::make('started_at')
                            ->label(trans('resources.project.started_at'))
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->nullable(),
                        DatePicker::make('estimated_project_end_date')
                            ->label(trans('resources.project.estimated_project_end_date'))
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->nullable(),
                        DatePicker::make('actual_project_end_date')
                            ->label(trans('resources.project.actual_project_end_date'))
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->nullable(),
                        TextInput::make('execution_time_years')
                            ->label(trans('resources.project.execution_time_years'))
                            ->numeric()
                            ->mask('99')
                            ->minLength(0)
                            ->maxLength(20)
                            ->default(0)
                            ->required(),
                        TextInput::make('execution_time_months')
                            ->label(trans('resources.project.execution_time_months'))
                            ->numeric()
                            ->mask('99')
                            ->minLength(0)
                            ->maxLength(12)
                            ->default(0)
                            ->required(),
                        TextInput::make('execution_time_days')
                            ->label(trans('resources.project.execution_time_days'))
                            ->numeric()
                            ->mask('99')
                            ->minLength(0)
                            ->maxLength(31)
                            ->default(0)
                            ->required(),
                        Section::make(trans('resources.project.edit.title'))
                            ->description(trans('resources.project.edit.description'))
                            ->schema([
                                Select::make('command_id')
                                    ->preload()
                                    ->forceSearchCaseInsensitive()
                                    ->label(trans('resources.project.edit.command-title'))
                                    ->options(fn (Get $get): Collection => Command::query()
                                        ->where('project_id', $this->record->id)
                                        ->pluck('number', 'id'))
                                    ->unique('activity_log', 'command_id')
                                    ->searchable()
                                    ->required(),
                                Textarea::make('description')
                                    ->label(trans('resources.project.description'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->action(function (array $data, Project $project): void {
                        $this->description = Arr::pull($data, 'description');
                        $this->commandId = Arr::pull($data, 'command_id');

                        $project->update(Arr::except($data, 'description'));
                    })
                    ->after(function (Project $project) {
                        $project->activities->last()->update([
                            'description' => $this->description,
                            'command_id' => $this->commandId,
                        ]);
                    })->modalWidth(50),
            ]),
        ];
    }
}
