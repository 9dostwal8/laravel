<?php

namespace App\Filament\Resources;

use App\Enums\AmenityStatus;
use App\Enums\InvestmentTypeEnum;
use App\Enums\LandAllocationTypeEnum;
use App\Enums\ProjectActivityAreaTypeEnum;
use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\Pages\Tabs\ProjectBasicInformation;
use App\Filament\Resources\ProjectResource\Pages\Tabs\ProjectPlace;
use App\Filament\Resources\ProjectResource\Pages\Tabs\ProjectVariants;
use App\Filament\Resources\ProjectResource\Pages\Tabs\ProjectWorkInformation;
use App\Filament\Resources\ProjectResource\RelationManagers\AlertsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\CommandsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\InvestorsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProgressesRelationManager;
use App\Models\ActivityArea;
use App\Models\ActivityType;
use App\Models\Area;
use App\Models\Department;
use App\Models\Investor;
use App\Models\LicensingAuthority;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Status;
use App\Traits\LangSwitcher;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Models\Country;
use Filament\Forms\Components\Select;

class ProjectResource extends Resource implements HasShieldPermissions
{
    use LangSwitcher,
        ProjectVariants,
        ProjectBasicInformation,
        ProjectPlace,
        ProjectWorkInformation,
        Pages\Tabs\ProjectAmenity;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-projects');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.project.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.project.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(! empty(get_user_sectors()), function ($query) {
                $query->whereHas('projectVariants', function ($query) {
                   $query->whereIn('activity_area_id', get_user_sectors());
                });
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    Step::make(trans('resources.project.basic_information'))
                        ->columns()
                        ->live()
                        ->schema(self::projectBasicInformation($form)),

                    Step::make('variants')
                        ->label(trans('resources.project.variants'))
                        ->columns()
                        ->live()
                        ->hidden(fn(Get $get) => $get('status') == 1)
                        ->schema(self::projectVariants($form)),

                    Step::make(trans('resources.project.project_place'))
                        ->columns()
                        ->hidden(fn(Get $get) => $get('status') == 1)
                        ->live()
                        ->schema(self::projectPlace($form)),

                    Step::make(trans('resources.project.project_work_information'))
                        ->columns()
                        ->hidden(fn(Get $get) => $get('status') == 1)
                        ->live()
                        ->schema(self::projectWorkInformation($form)),

                    Step::make(trans('resources.amenity.plural'))
                        ->columns()
                        ->hidden(fn(Get $get) => $get('status') == 1)
                        ->live()
                        ->schema(self::projectAmenity($form)),

                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString()
                    ->skippable(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_number')
                    ->label(trans('resources.project.license_number'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(trans('resources.project.status')),
                TextColumn::make('file_number')
                    ->label(trans('resources.project.file_number'))
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('projectVariants')
                    ->state(function (Project $record) {
                        $string = '';
                        foreach ($record->projectVariants as $variant) {

                            if (empty($variant->activityArea)) continue;

                            $name = self::getTranslation($variant->activityArea->name);

                            $string .= "$name | ";
                        }
                        return $string;
                    })
                    ->label(trans('resources.variants.plural')),

                TextColumn::make('projectVariants.capital_dinar')
                    ->numeric( locale: 'en')
                    ->state((fn(Project $record) => $record->projectVariants->sum(fn($variant) => $variant->capital_dinar ?? 0)))
                    ->label(trans('resources.project.finance.capital_dinar')),

                TextColumn::make('projectVariants.capital_dollar')
                    ->numeric(2, locale: 'en')
                    ->state((fn(Project $record) => $record->projectVariants->sum(fn($variant) => $variant->capital_dollar ?? 0)))
                    ->label(trans('resources.project.finance.capital_dollar')),

                TextColumn::make('project_name')
                    ->label(trans('resources.project.project_name'))
                    ->formatStateUsing(fn($record) => self::getTranslation($record?->project_name))
                    ->sortable()
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('project_name->ckb', 'like', "%$search%")
                            ->orWhere('project_name->en', 'like', "%$search%");
                    }),
                TextColumn::make('organization.name')
                    ->label(trans('resources.organization.single'))
                    ->formatStateUsing(fn($record) => self::getTranslation($record?->organization->name))
                    ->sortable(),
                TextColumn::make('execution_time_years')
                    ->label(trans('resources.project.execution_time_years'))
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label(trans('resources.state.single'))
                    ->formatStateUsing(fn($record) => self::getTranslation($record?->state->name))
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label(trans('resources.project.started_at'))
                    ->toggleable()
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('estimated_project_end_date')
                    ->label(trans('resources.project.estimated_project_end_date'))
                    ->toggleable()
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('meter_area')
                    ->label(trans('resources.project.meter_area'))
                    ->numeric(locale: 'en')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('hectare_area')
                    ->label(trans('resources.project.hectare_area'))
                    ->numeric(locale: 'en')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('hectare_area')
                    ->label(trans('resources.project.hectare_area'))
                    ->numeric(locale: 'en')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('Variants')
                    ->state(fn(Project $record) => number_format($record->projectVariants()->sum('capital_dollar') ?? 0))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(trans('resources.project.finance.capital_dollar')),

                TextColumn::make('progress')
                    ->state(fn(Project $record) => $record->progresses()->sum('progress_percentage') ?? 0)
                    ->formatStateUsing(fn($state) => "$state %")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(trans('resources.progress.single')),

                TextColumn::make('licence_received_at')
                    ->label(trans('resources.project.licence_received_at'))
                    ->date('Y/m/d'),

                //
            ])
            ->filters([
                SelectFilter::make('status')
                    ->forceSearchCaseInsensitive()
                    ->preload()
                    ->label(trans('resources.project.status'))
                    ->options(ProjectStatus::class)
                    ->multiple(),
                SelectFilter::make('project_state')
                    ->forceSearchCaseInsensitive()
                    ->preload()
                    ->label(trans('resources.project.state'))
                    ->options(function () {
                        $data = [];
                        $items = Status::pluck('name', 'id')->toArray();
                        foreach ($items as $id => $name) {
                            $data[$id] = self::getTranslation($name);
                        }

                        return $data;
                    })
                    ->multiple(),

                Filter::make('licence_received_at')
                    ->form([
                        DatePicker::make('licence_received_at_from')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.licence_received_at_from')),
                        DatePicker::make('licence_received_at_until')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.licence_received_at_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['licence_received_at_from'] && $data['licence_received_at_until'],
                                fn(Builder $query, $date): Builder => $query->whereBetween('licence_received_at', [$data['licence_received_at_from'],$data['licence_received_at_until']]),
                            );
                    })
                    ->columnSpan([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->columns(),

                SelectFilter::make('activity_area_id')
                    ->label(trans('resources.activity-area.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        if (empty($filter->getState()['values'])) {
                            return null;
                        }

                        $values = $filter->getState()['values'];
                        $investors = ActivityArea::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->options( function () {
                        $data = [];
                        foreach (ActivityArea::pluck('name', 'id')->toArray() as $key => $name) {
                            if (empty(get_user_sectors()) || in_array($key, get_user_sectors())) {
                                $data[$key] = self::getTranslation($name);
                            }
                        }
                        return $data;
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereHas(
                                'projectVariants',
                                fn(Builder $query) => $query->whereHas(
                                    'activityArea',
                                    fn(Builder $query) => $query->whereIn('id', $data['values'])
                                )
                            );
                        }
                    })
                    ->multiple(),

                SelectFilter::make('activity_area_type')
                    ->label(trans('resources.activity_area_type'))
                    ->options(ProjectActivityAreaTypeEnum::class)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->where(function (Builder $query) use ($data) {
                                foreach ($data['values'] as $type) {
                                    if ($type == 1) { // Single projects: only have type=1, null, or 0 variants
                                        $query->orWhere(function (Builder $query) {
                                            $query->whereHas('projectVariants', function (Builder $query) {
                                                $query->where(function (Builder $query) {
                                                    $query->where('type', 1)
                                                          ->orWhereNull('type')
                                                          ->orWhere('type', 0);
                                                });
                                            })
                                            ->whereDoesntHave('projectVariants', function (Builder $query) {
                                                $query->where('type', 2);
                                            });
                                        });
                                    } elseif ($type == 2) { // Joint projects: have at least one type=2 variant
                                        $query->orWhereHas('projectVariants', function (Builder $query) {
                                            $query->where('type', 2);
                                        });
                                    }
                                }
                            });
                        }
                    })
                    ->multiple(),

                SelectFilter::make('organization_id')
                    ->label(trans('resources.organization.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        $values = $filter->getState()['values'];
                        if (empty($values)) {
                            return null;
                        }

                        $investors = Organization::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->relationship('organization', self::getModelName())
                    ->preload()
                    ->multiple(),

                SelectFilter::make('department_id')
                    ->label(trans('resources.department.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        $values = $filter->getState()['values'];
                        if (empty($values)) {
                            return null;
                        }

                        $investors = Department::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->relationship('department', self::getModelName())
                    ->multiple(),
                SelectFilter::make('area_id')
                    ->label(trans('resources.area.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        $values = $filter->getState()['values'];
                        if (empty($values)) {
                            return null;
                        }

                        $investors = Area::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->relationship('area', self::getModelName())
                    ->multiple(),

                Filter::make('village')
                    ->form([
                        TextInput::make('village')
                            ->label(trans('resources.project.village'))
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['village'],
                                fn(Builder $query, $date): Builder => $query->where('village', $data['village']),
                            );
                    })
                    ->columnSpan([
                        'sm' => 1,
                        'xl' => 1,
                        '2xl' => 1,
                    ])
                    ->columns(1),

                Filter::make('license_number')
                    ->form([
                        TextInput::make('license_number_value')
                            ->label(trans('resources.project.license_number'))
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['license_number_value'],
                                fn(Builder $query, $date): Builder => $query->where('license_number', $data['license_number_value']),
                            );
                    })
                    ->columnSpan([
                        'sm' => 1,
                        'xl' => 1,
                        '2xl' => 1,
                    ])
                    ->columns(1),

                SelectFilter::make('licensing_authority_id')
                    ->label(trans('resources.project.licensing_authority'))
                    ->options(function () {
                        $data = [];

                        $items = LicensingAuthority::whereHas('organizations', function ($q) {
                            if (auth()->user()->isAdmin()) {
                                return $q;
                            }

                            return $q->where('organization_id', \auth()->user()->organization_id);
                        })->get();

                        foreach ($items as $item) {
                            $data[$item->id] = $item->name[app()->getLocale()] ?? $item->name['ckb'];
                        }

                        return $data;
                    }),

                SelectFilter::make('investment_type')
                    ->forceSearchCaseInsensitive()
                    ->preload()
                    ->label(trans('resources.project.investment_type'))
                    ->options(InvestmentTypeEnum::class)
                    ->multiple(),
                SelectFilter::make('investor_id')
                    ->label(trans('resources.investor.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        $values = $filter->getState()['values'];
                        if (empty($values)) {
                            return null;
                        }

                        $investors = Investor::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->relationship('investors', 'name->ckb')
                    ->multiple(),
                SelectFilter::make('land_granting_organization')
                    ->label(trans('resources.project.land_granting_organization'))
                    ->options(trans('resources.ministries')),

                    SelectFilter::make('activity_type_id')
                    ->label(trans('resources.activity-type.single'))
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->indicateUsing(function (SelectFilter $filter): ?string {
                        // Get the selected model records via $filter->getState()['values']
                        // return the record name based on user language

                        if (empty($filter->getState()['values'])) {
                            return null;
                        }

                        $values = $filter->getState()['values'];
                        $investors = ActivityType::query()->whereIn('id', $values)->get();

                        $label = '';
                        foreach ($investors as $key => $investor) {
                            $label .= self::getTranslation($investor->name);

                            if ($key < ($investors->count() - 1)) {
                                $label .= ' & ';
                            }
                        }

                        return $filter->getLabel() . ': ' . $label;
                    })
                    ->options(
                        function () {
                            return ActivityType::all()->pluck(self::getModelNameDotPoint(), 'id')
                                ->filter(fn($area) => $area !== null)
                                ->toArray();
                        }
                    )
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereHas(
                                'projectVariants',
                                fn(Builder $query) => $query->whereHas(
                                    'activityType',
                                    fn(Builder $query) => $query->whereIn('id', $data['values'])
                                )
                            );
                        }
                    })
                    ->multiple(),

                Filter::make('first_customs_date')
                    ->form([
                        DatePicker::make('first_customs_date_from')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.first_customs_date_from')),
                        DatePicker::make('first_customs_date_until')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.first_customs_date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['first_customs_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('first_customs_date', '>=', $data['first_customs_date_from']),
                            )
                            ->when(
                                $data['first_customs_date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('first_customs_date', '<=', $data['first_customs_date_until']),
                            );
                    })
                    ->columnSpan([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->columns(),


               

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.created_from')),
                        DatePicker::make('created_until')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $data['created_until']),
                            );
                    })
                    ->columnSpan([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->columns(),

                    // update_at
                    Filter::make('updated_at')
                    ->form([
                        DatePicker::make('updated_at_from')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.updated_at_from')),
                        DatePicker::make('updated_at_until')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->label(trans('resources.updated_at_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_at_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $data['updated_at_from']),
                            )
                            ->when(
                                $data['updated_at_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $data['updated_at_until']),
                            );
                    })
                    ->columnSpan([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->columns(),

                Filter::make('countries')
                    ->form([
                        Select::make('countries')
                            ->label(trans('resources.countries'))
                            ->preload()
                            ->options(function () {
                                $data = [];
                                foreach (Country::all() as $item) {
                                    $data[$item->id] = self::getTranslation($item->name);
                                }
                                return $data;
                            })
                            ->multiple(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['countries'], function (Builder $query) use ($data) {
                            $query->whereHas('originalCountries', function (Builder $query) use ($data) {
                                $query->whereIn('country_id', $data['countries']);
                            });
                        });
                    }),

                QueryBuilder::make()
                    ->constraintPickerColumns()
                    ->constraintPickerWidth('2xl')
                    ->constraints([

                        NumberConstraint::make('projectVariants.capital_dinar')
                            ->label(trans('resources.project.finance.capital_dinar')),

                        NumberConstraint::make('projectVariants.currency_rate')
                            ->label(trans('resources.project.finance.currency_rate')),

                        NumberConstraint::make('projectVariants.capital_dollar')
                            ->label(trans('resources.project.finance.capital_dollar')),

                        NumberConstraint::make('projectVariants.loan_fund')
                            ->label(trans('resources.project.finance.loan_fund')),
                        NumberConstraint::make('projectVariants.non_loan_fund')
                            ->label(trans('resources.project.finance.non_loan_fund')),
                        NumberConstraint::make('execution_time_years')
                            ->label(trans('resources.project.execution_time_years')),
                        NumberConstraint::make('execution_time_months')
                            ->label(trans('resources.project.execution_time_months')),
                        NumberConstraint::make('hectare_area')
                            ->label(trans('resources.project.hectare_area')),
                        NumberConstraint::make('meter_area')
                            ->label(trans('resources.project.meter_area')),

                        NumberConstraint::make('kurdistan_fixed_workforce_count')
                            ->label(trans('resources.project.fixed_workforce_number.kurdistan_full')),
                        NumberConstraint::make('foreign_fixed_workforce_count')
                            ->label(trans('resources.project.fixed_workforce_number.foreign_full')),
                        NumberConstraint::make('iraq_fixed_workforce_count')
                            ->label(trans('resources.project.fixed_workforce_number.iraq_full')),
                        NumberConstraint::make('seperated_areas_fixed_workforce_count')
                            ->label(trans('resources.project.fixed_workforce_number.seperated_areas_full')),

                        NumberConstraint::make('kurdistan_temporary_workforce_count')
                            ->label(trans('resources.project.temporary_workforce_number.kurdistan_full')),
                        NumberConstraint::make('foreign_temporary_workforce_count')
                            ->label(trans('resources.project.temporary_workforce_number.foreign_full')),
                        NumberConstraint::make('iraq_temporary_workforce_count')
                            ->label(trans('resources.project.temporary_workforce_number.iraq_full')),
                        NumberConstraint::make('seperated_areas_temporary_workforce_count')
                            ->label(trans('resources.project.temporary_workforce_number.seperated_areas_full')),

                        DateConstraint::make('requested_at')
                            ->label(trans('resources.project.requested_at')),
                        DateConstraint::make('licence_received_at')
                            ->label(trans('resources.project.licence_received_at')),
                        DateConstraint::make('land_delivered_at')
                            ->label(trans('resources.project.land_delivered_at')),
                        DateConstraint::make('started_at')
                            ->label(trans('resources.project.started_at')),
                        DateConstraint::make('estimated_project_end_date')
                            ->label(trans('resources.project.estimated_project_end_date')),
                        DateConstraint::make('actual_project_end_date')
                            ->label(trans('resources.project.actual_project_end_date')),
                    ]),

            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Action::make('activities')->label(trans('resources.project.activities-btn'))->url(fn($record) => ProjectResource::getUrl('activities', ['record' => $record])),
                    Action::make('Statistics')->label(trans('resources.project.statistics-btn'))->url(fn($record) => ProjectResource::getUrl('summary', ['record' => $record])),
                    Action::make('Print')->label(trans('resources.project.print-btn'))->url(fn($record) => route('project.summary', $record)),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\BulkAction::make('export_excel_all')
                        ->label(trans('resources.export_all_projects'))
                     //   ->action(fn($records) => 'form fields')
                        ->action(function ($records) {
                            if (auth()->user()->hasPermissionTo('export_all_project')) {
                                return self::exportToExcel($records);
                            } else {
                                Notification::make('export_all_projects_error')
                                    ->danger()
                                    ->title(trans('resources.export_all_projects_error_title'))
                                    ->send();
                            }
                        }),

//                     ExportBulkAction::make('export_table')
//                         ->exports([
//                             ExcelExport::make('all')
//                                 ->queue()
//                                 ->fromForm()
//                                 ->withColumns([
//                                     Column::make('projectVariants')
//                                         ->formatStateUsing(fn() => 'Miran')
//                                 ]),
//                         ])
//                         ->label(trans('resources.export_to_excel')),

                    ExportBulkAction::make('export_amenities')
                        ->exports([
                            ExcelExport::make('export_amenities_columns')->withColumns([

                                Column::make('created_at')
                                    ->heading(trans('resources.project.project_name'))
                                    ->formatStateUsing(fn($record) => self::getTranslation($record->project_name)),

                                Column::make('projectVariants')
                                    ->formatStateUsing(function ($state) {
                                        $name = '';
                                        foreach ($state as $item) {
                                            $name .= ' ' . self::getTranslation($item->activityArea->name) . ',';
                                        }
                                        return $name;
                                    })
                                    ->heading(trans('resources.activity-area.plural')),

                                Column::make('amenities')
                                    ->formatStateUsing(function ($state) {
                                        $name = '';
                                        foreach ($state as $item) {
                                            $name .= ' ' . self::getTranslation($item->amenity->name) . ',';
                                        }
                                        return $name;
                                    })
                                    ->heading(trans('resources.amenity.plural')),
                            ])
                        ])
                        ->label(trans('resources.project_by_amenities')),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($action) {

                            foreach ($action->getRecords() as $record) {

                                $relations = [
                                    $record->projectVariants()->count(),
                                    $record->amenities()->count(),
                                    $record->alerts()->count(),
                                    $record->commands()->count(),
                                    $record->documents()->count(),
                                    $record->investors()->count(),
                                    $record->letters()->count(),
                                    $record->notes()->count(),
                                    $record->progresses()->count(),
                                ];

                                foreach ($relations as $relation) {
                                    if ($relation >= 1) {
                                        Notification::make()
                                            ->danger()
                                            ->persistent()
                                            ->title(trans('resources.project_has_attachments_cant_be_deleted'))
                                            ->send();
                                        $action->cancel();
                                        $action->halt();
                                    }
                                }

                            }
                        }),

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LettersRelationManager::class,
            CommandsRelationManager::class,
            DocumentsRelationManager::class,
            ProgressesRelationManager::class,
            NotesRelationManager::class,
            AlertsRelationManager::class,
            InvestorsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'activities' => Pages\ListProjectActivities::route('/{record}/activities'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'summary' => Pages\ProjectSummary::route('/{record}/summary'),
        ];
    }

    private static function exportToExcel($records)
    {
        $data = [];
        $fields = [];
        $flat_fields = self::form((new Pages\CreateProject())
            ->mksGetForm()
            ->getLivewire()->form)
            ->getFlatFields(true);

        foreach ($flat_fields as $flat_field) {
            if (
                $flat_field->getStatePath(false) == 'projectVariants' ||
                $flat_field->getStatePath(false) == 'amenities'
            ) {
                continue;
            }
            $fields[$flat_field->getStatePath(false)] = strip_tags($flat_field->getLabel());
        }

        $names = array_keys($fields);
        $headers = array_values($fields);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $projectVariantHeaders = [
            'activity_area_id' => trans('resources.activity-area.single'),
            'activity_type_id' => trans('resources.activity-type.single'),
            'type' => trans('resources.project.activity-area-type'),
            'currency_rate' => trans('resources.project.finance.currency_rate'),
            'capital_dinar' => trans('resources.project.finance.capital_dinar'),
            'capital_dollar' => trans('resources.project.finance.capital_dollar'),
            'loan_fund' => trans('resources.project.finance.loan_fund'),
            'non_loan_fund' => trans('resources.project.finance.non_loan_fund'),
        ];

        $amenitiesHeaders = [
            'amenity_id' => trans('resources.amenity.single'),
            'counts' => trans('resources.amenity.counts'),
            'status' => trans('resources.amenity.status'),
            'production_rate' => trans('resources.amenity.production_rate'),
            'ranking' => trans('resources.ranking'),
            'product_type' => trans('resources.product_type'),
            'amount' => trans('resources.amount'),
            'measurement_unit' => trans('resources.measurement_unit'),
            'description' => trans('resources.amenity.description'),
        ];

        $names = array_merge($names, array_keys($projectVariantHeaders), array_keys($amenitiesHeaders));

        foreach ($records as $key => $record) {

            if (! empty($data)) {
                $key = array_key_last($data) + 1;
            }

            foreach ($names as $name) {
                switch ($name) {
                    case 'land_granting_organization':
                        $data[$key][] = trans('resources.ministries')[$record->land_granting_organization] ?? null;
                        break;
                    case 'investor_id':
                        $items = $record->investors()->pluck('name')->toArray();
                        $investors = null;
                        foreach ($items as $item) {
                            $investors .= self::getTranslation($item) . ' - ';
                        }
                        $data[$key][] = $investors;
                        break;
                    case 'project_name.ckb': $data[$key][] = $record->project_name['ckb'] ?? null;
                        break;
                    case 'project_name.ar': $data[$key][] = $record->project_name['ar'] ?? null;
                        break;
                    case 'project_name.en': $data[$key][] = $record->project_name['en'] ?? null;
                        break;
                    case 'project_state':
                        $data[$key][] = static::getState($record, $names, $name);
                        break;
                    case 'status':
                        $data[$key][] = static::getStatus($record, $names, $name);
                        break;
                    case 'is_brand':
                        $data[$key][] = self::getIsBrand($record, $names, $name);
                        break;
                    case 'decision_of_committee':
                        $data[$key][] = self::decisionOfCommittee($record, $names, $name);
                        break;
                    case 'decision_of_chairman_committee':
                        $data[$key][] = self::decisionOfChairmanCommittee($record, $names, $name);
                        break;
                    case 'licensing_authority_id':
                        $data[$key][] = self::licensingAuthority($record, $names, $name);
                        break;
                    case 'investment_type':
                        $data[$key][] = self::investmentType($record, $names, $name);
                        break;
                    case 'bank_guarantee':
                        $data[$key][] = self::bankGuarantee($record, $names, $name);
                        break;
                    case 'state_id':
                        $data[$key][] = self::state($record, $names, $name);
                        break;
                    case 'department_id':
                        $data[$key][] = self::department($record, $names, $name);
                        break;
                    case 'area_id':
                        $data[$key][] = self::area($record, $names, $name);
                        break;
                    case 'land_number':
                        $data[$key][] = self::landNumber($record, $names, $name);
                        break;
                    case 'type_of_land_allocation':
                        $data[$key][] = self::typeOfLandAllocation($record, $names, $name);
                        break;
                    case 'countries':
                        $data[$key][] = self::getCountries($record);
                        break;
                    case 'location':
                        $data[$key][] = self::location($record, $names, $name);
                        break;
                    case 'activity_area_id':
                        $data[$key][] = null;
                        $projectVariants = [];
                        $amenities = [];

                        foreach ($record->projectVariants as $v_key => $variant) {
                            $activityArea = $variant->activityArea?->name ?? null;
                            $activityType = $variant->activityType?->name ?? null;
                            $variantType = $variant->type ?? null;
                            $projectVariants[$v_key][71] = ! empty($activityArea) ? self::getTranslation($activityArea) : null;
                            $projectVariants[$v_key][72] = ! empty($activityType) ? self::getTranslation($activityType) : null;
                            $projectVariants[$v_key][73] = ! empty($variantType) ? ProjectActivityAreaTypeEnum::tryFrom($variantType)->getLabel() : null;
                            $projectVariants[$v_key][74] = $variant->currency_rate;
                            $projectVariants[$v_key][75] = $variant->capital_dinar;
                            $projectVariants[$v_key][76] = $variant->capital_dollar;
                            $projectVariants[$v_key][77] = $variant->loan_fund;
                            $projectVariants[$v_key][78] = $variant->non_loan_fund;
                        }

                        foreach ($projectVariants as $n_key => $n_variants) {
                            $n_key = $n_key + 1;
                            $data[ $key + $n_key] = array_fill(0, count($names), null);
                            foreach ($n_variants as $mnv_key => $n_variant) {
                                $data[ $key + $n_key][$mnv_key] = $n_variant;
                            }
                        }

                        foreach ($record->amenities as $a_key => $amenity) {
                            $amenityName = $amenity->amenity?->name ?? null;
                            $status = $amenity->status ?? null;
                            $ranking = $amenity->ranking ?? null;
                            switch ($amenity->ranking) {
                                case 1: $ranking = trans('resources.low'); break;
                                case 2: $ranking = trans('resources.mid'); break;
                                case 3: $ranking = trans('resources.high'); break;
                            }

                            $amenities[$a_key][79] = ! empty($amenityName) ? self::getTranslation($amenityName) : null;
                            $amenities[$a_key][80] = $amenity->counts;
                            $amenities[$a_key][81] = ! empty($status) ? AmenityStatus::tryFrom($status)->getLabel() : null;
                            $amenities[$a_key][82] = $amenity->production_rate;
                            $amenities[$a_key][83] = $ranking;
                            $amenities[$a_key][84] = $amenity->product_type;
                            $amenities[$a_key][85] = $amenity->amount;
                            $amenities[$a_key][86] = $amenity->measurement_unit;
                            $amenities[$a_key][87] = $amenity->description;

                        }

                        foreach ($amenities as $m_key => $m_amenities) {
                            $m_key = $n_key + $m_key + 1;
                            $data[ $key + $m_key] = array_fill(0, count($names), null);
                            foreach ($m_amenities as $msa_key => $m_amenity) {
                                $data[ $key + $m_key][$msa_key] = $m_amenity;
                            }
                        }

                        break;
                    default:
                        $data[$key][] = $record?->{$name};
                }
            }
        }

        

        $headers = array_merge($headers, array_values($projectVariantHeaders), array_values($amenitiesHeaders));
        $sheet->fromArray($headers);
        $sheet->fromArray($data, null, 'A2');
        $writer = new Xlsx($spreadsheet);
        $fileName = 'exported_data.xlsx';

        return Response::streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    private static function getStatus(Project $record, array $names, int|string $name)
    {
        if (! empty($record->{$name})) {
            return ProjectStatus::tryFrom($record->{$name}->value)->getLabel();
        }

        return null;
    }

    private static function getIsBrand(Project $record, array $names, int|string $name)
    {
        return $record->{$name} == 1 ? trans('resources.yes') : trans('resources.no');
    }

    private static function getCountries(Project $record)
    {
        if (!$record->originalCountries || $record->originalCountries->isEmpty()) {
            return null;
        }

        $countryNames = $record->originalCountries
            ->pluck('name.' . app()->getLocale())
            ->filter()
            ->unique()
            ->values();

        return $countryNames->isEmpty() ? null : $countryNames->implode(' + ');
    }

    private static function decisionOfCommittee(Project $record, array $names, int|string $name)
    {
        return $record->{$name} == 1 ? trans('resources.yes') : trans('resources.no');
    }

    private static function decisionOfChairmanCommittee(Project $record, array $names, int|string $name)
    {
        return $record->{$name} == 0 ? trans('resources.acceptable') : trans('resources.not_acceptable');
    }

    private static function licensingAuthority(Project $record, array $names, int|string $name)
    {
        $item = LicensingAuthority::find($record->{$name});

        if (empty($item)) {
            return null;
        }

        return self::getTranslation($item->name);
    }

    private static function investmentType(Project $record, array $names, int|string $name)
    {
        return InvestmentTypeEnum::tryFrom($record->{$name}->value)->getLabel();
    }

    private static function bankGuarantee(Project $record, array $names, int|string $name)
    {
        return $record->{$name} == 1 ? trans('resources.yes') : trans('resources.no');
    }

    private static function state(Project $record, array $names, int|string $name)
    {
        $state = $record->state?->name ?? null;

        return !empty($state) ? self::getTranslation($state) : null;
    }

    private static function department(Project $record, array $names, int|string $name)
    {
        $department = $record->department?->name ?? null;

        return !empty($department) ? self::getTranslation($department) : null;
    }

    private static function area(Project $record, array $names, int|string $name)
    {
        $area = $record->area?->name ?? null;

        return !empty($area) ? self::getTranslation($area) : null;
    }

    private static function landNumber(Project $record, array $names, int|string $name)
    {
        return is_array($record->{$name}) ? implode(', ', $record->{$name}) : null;
    }

    private static function typeOfLandAllocation(Project $record, array $names, int|string $name)
    {
        if (!empty($record->type_of_land_allocation)) {
            return LandAllocationTypeEnum::tryFrom($record->{$name})->getLabel();
        }

        return null;
    }

    private static function location(Project $record, array $names, int|string $name)
    {
        if (is_array($record->location) && count($record->location) == 2) {
            return "Lat: {$record->location['lat']} - Lng: {$record->location['lng']}";
        }

        return null;
    }

    private static function getState(Project $record, array $names, int|string $name)
    {
        $state = Status::find($record->{$name});

        if (! empty($state) ) {
            return self::getTranslation($state->name);
        }

        return null;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'export_all',
        ];
    }

}


