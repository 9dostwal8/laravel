<?php

namespace App\Filament\Resources\ProjectResource\Pages\Tabs;

use App\Enums\InvestmentTypeEnum;
use App\Enums\ProjectStatus;
use App\Models\Country;
use App\Models\LicensingAuthority;
use App\Models\Status;
use App\Services\TranslatableField;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Http\Request;

trait ProjectBasicInformation
{
    public static function projectBasicInformation($form)
    {
        return [
            Select::make('show')
                ->required()
                ->hidden(!auth()->user()->isAdmin())
                ->options([
                    0 => trans('resources.inactive'),
                    1 => trans('resources.active'),
                ])
                ->label(trans('resources.show')),

            Select::make('status')
                ->label(trans('resources.project.status'))
                ->options(ProjectStatus::class)
                ->searchable()
                ->reactive()
                ->live()
                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                    $set('project_state', null);
                    $set('licensing_authority_id', null);
                    $set('licence_received_at', null);
                    $set('license_number', null);
                    if ($get('status') != 5) {
                        $set('cancellation_number', null);
                        $set('cancellation_date', null);
                        $set('cancellation_transfer_land', null);
                        $set('cancellation_attachment', null);
                    }

                    if ($get('status') == 8) {
                        $set('land_granting_organization', null);
                    }
                })
                ->afterStateUpdated(fn(Component $component) => $component->render())
                ->required(),

            Select::make('project_state')
                ->label(trans('resources.project.state'))
                ->options(function ($record, Get $get) {

                    if (empty($get('status'))) return [];

                    $data = [];
                    $check = Status::where('type', self::class)
                        ->where('project_type', $get('status'));

                    if ($check->exists()) {
                        $names = $check->pluck('name', 'id')->toArray();

                        foreach ($names as $key => $name) {
                            $data[$key] = self::getTranslation($name);
                        }

                        return $data;
                    }

                    return [];
                })
                ->searchable()
                ->preload()
                ->required(),

            // Status => 1

            Select::make('plan_of_ministry_mines')
                ->label(trans('resources.plan_of_ministry_mines'))
                ->visible(fn(Get $get) => $get('status') == 1)
                ->options([
                    0 => trans('resources.no'),
                    1 => trans('resources.yes'),
                ])
                ->required(),

            Select::make('is_brand')
                ->label(trans('resources.is_brand'))
                ->visible(fn(Get $get) => $get('status') == 1)
                ->live()
                ->options([
                    0 => trans('resources.no'),
                    1 => trans('resources.yes')
                ])
                ->required(),

            TextInput::make('brand_type')
                ->label(trans('resources.brand_type'))
                ->visible(fn(Get $get) => $get('status') == 1 && $get('is_brand') == 1)
                ->required(),

            Select::make('decision_of_committee')
                ->label(trans('resources.decision_of_committee'))
                ->visible(fn(Get $get) => $get('status') == 1)
                ->options([
                    0 => trans('resources.acceptable'),
                    1 => trans('resources.not_acceptable')
                ])
                ->required(),

            Select::make('decision_of_chairman_committee')
                ->label(trans('resources.decision_of_chairman_committee'))
                ->visible(fn(Get $get) => $get('status') == 1)
                ->options([
                    0 => trans('resources.acceptable'),
                    1 => trans('resources.not_acceptable')
                ])
                ->required(),


            // Status => 1


            // Status => 5

            TextInput::make('cancellation_number')
                ->label(trans('resources.cancellation_number'))
                ->visible(fn(Get $get) => $get('status') == 5)
                ->required(),

            DatePicker::make('cancellation_date')
                ->native(false)
                ->label(trans('resources.cancellation_date'))
                ->visible(fn(Get $get) => $get('status') == 5)            ,

            TextInput::make('cancellation_transfer_land')
                ->label(trans('resources.cancellation_transfer_land'))
                ->visible(fn(Get $get) => $get('status') == 5)
                ->required(),

            FileUpload::make('cancellation_attachment')
                ->label(trans('resources.cancellation_attachment'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                ->visibility('private')
                ->visible(fn(Get $get) => $get('status') == 5)
                ->directory('projects-cancellation-docs'),

            // Status => 5


//            TextInput::make('old_file_number')
//                ->label(trans('resources.project.old_file_number'))
//                ->required()
//                ->mask('9999999999')
//                ->minLength(0)
//                ->hidden(fn(Get $get) => $get('status') == 1)
//                ->disabled(fn(Get $get) => $get('status') == 8)
//                ->maxLength(255),
//
            TextInput::make('file_number')
                ->label(trans('resources.project.file_number'))
                ->mask('***-999999999')
                ->default(function () {
                    return auth()->user()?->organization->slug . '-' . mt_rand(10000000, 99999999);
                })
//                ->hidden(fn(Get $get) => $get('status') == 1)
//                ->disabled(fn(Get $get) => $get('status') == 8)
                ->maxLength(255),

            TextInput::make('license_number')
                ->label(trans('resources.project.license_number'))
                ->required()
                ->minLength(0)
                ->maxLength(255)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->hidden(fn(Get $get) => $get('status') == 1 || $get('status') == 8),

            Select::make('licensing_authority_id')
                // ->relationship('licensingAuthorities', 'name')
                ->label(trans('resources.licensing-authorities.plural'))
                ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                ->options(function () {
                    $data = [];
                    $items = LicensingAuthority::whereHas('organizations', function ($q) {
                        $q->where('organization_id', \auth()->user()->organization_id);
                    })->get();

                    foreach ($items as $item) {
                        $data[$item->id] = $item->name[app()->getLocale()] ?? $item->name['ckb'];
                    }

                    return $data;
                })
                ->hidden(fn(Get $get) => $get('status') == 1)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->preload(),

//                            Fieldset::make(trans('resources.project.company_name'))
//                                ->schema(TranslatableField::make('company_name', ar: true)),
            Fieldset::make(trans('resources.project.project_name'))
                ->schema(TranslatableField::make('project_name', ar: true, en: true)),

            Section::make([

                Select::make('investor_id')
                    ->label(trans('resources.investor.single'))
                    ->relationship('investors', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                    ->searchable(['name->ckb', 'name->en'])
                    ->disabled()
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->multiple(),

                Select::make('investment_type')
                    ->label(trans('resources.project.investment_type'))
                    ->options(InvestmentTypeEnum::class)
                    ->required(),


                Repeater::make('countries')
                    ->relationship()
                    ->visible(fn(Get $get) => $get('investment_type') == InvestmentTypeEnum::JOINT->value || $get('investment_type') == InvestmentTypeEnum::FOREIGN->value)
                    ->simple(
                        Select::make('country_id')
                                ->label(trans('resources.investment_country'))
                                ->searchable()
                                ->options(function () {
                                    $data = [];
                                    $items = Country::get();
                                    foreach ($items as $item) {
                                        $data[$item->id] = $item->name[app()->getLocale()] ?? $item->name['ckb'];
                                    }
                                    return $data;
                                }),
                    )
                    ->label(trans('resources.countries')),

                TextInput::make('execution_time_years')
                    ->label(trans('resources.project.execution_time_years'))
                    ->numeric()
                    ->mask('99')
                    ->minLength(0)
                    ->maxLength(20)
                    ->default(0)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->required(),

                TextInput::make('execution_time_months')
                    ->label(trans('resources.project.execution_time_months'))
                    ->numeric()
                    ->mask('99')
                    ->minLength(0)
                    ->maxLength(12)
                    ->default(0)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->required(),

                TextInput::make('execution_time_days')
                    ->label(trans('resources.project.execution_time_days'))
                    ->numeric()
                    ->mask(99)
                    ->minLength(0)
                    ->maxLength(31)
                    ->default(0)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->required(),

                TextInput::make('meter_area')
                    ->label(trans('resources.project.meter_area'))
                    ->afterStateUpdated(function (Request $request, Get $get, Set $set, $state) {
                        $state = str_replace(',', '', $state);
                        if (!is_numeric($state)) return;

                        $updatedByMeterArea = array_key_exists(
                            'data.hectare_area',
                            current($request->input('components'))['updates']
                        );

                        if (!$updatedByMeterArea) {
                            $state = str_replace(',', '', $state);
                           // $set('hectare_area', empty($state) ? null : number_format($state / 2500, decimals: 5) );
                            $set('hectare_area', empty($state) ? null : smart_number($state / 2500) );
                        }
                    })
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-on:input' => RawJs::make("
                        let input = event.target.value.replace(/[^0-9.]/g, '');
                        if(input != ''){
                            let number = (input);
                            let formatted = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                            event.target.value = formatted;
                        }
                    "),
                    ])
                    ->live(onBlur: true)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->default(0),

                TextInput::make('hectare_area')
                    ->label(trans('resources.project.hectare_area'))
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-on:input' => RawJs::make("
                        let input_2 = event.target.value.replace(/[^0-9.]/g, '');
                        if(input_2 != ''){
                            let number_2 = (input_2);
                            let formatted_2 = number_2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                            event.target.value = formatted_2;
                        }
                    "),
                    ])
                    ->afterStateUpdated(function (Request $request, Set $set, $state) {
                        $state = str_replace(',', '', $state);
                        if (!is_numeric($state)) return;

                        $updatedByMeterArea = array_key_exists(
                            'data.meter_area',
                            current($request->input('components'))['updates']
                        );

                        if (!$updatedByMeterArea) {
                            $state = str_replace(',', '', $state);
                          //  $set('meter_area', empty($state) ? null : number_format($state * 2500, decimals: 5));
                            $set('meter_area', empty($state) ? null : smart_number($state * 2500));
                        }
                    })
                    ->default(0)
                    ->live(onBlur: true)
                    ->hidden(fn(Get $get) => $get('status') == 1),

                DatePicker::make('requested_at')
                    ->label(trans('resources.project.requested_at'))
                    ->default(null)
                    ->displayFormat('d-m-Y')
                    //  ->beforeOrEqual('licence_received_at')
                   // ->hidden(fn(Get $get) => $get('status') == 1)
                    ->native(false),

                DatePicker::make('first_customs_date')
                    ->label(trans('resources.first_customs_date'))
                    ->default(null)
                    ->displayFormat('d-m-Y')
                    //  ->afterOrEqual('requested_at')
                    ->disabled(fn(Get $get) => $get('status') == 8)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->native(false),

                DatePicker::make('last_customs_date')
                    ->label(trans('resources.last_customs_date'))
                    ->default(null)
                    ->displayFormat('d-m-Y')
                    // ->afterOrEqual('licence_received_at')
                    ->disabled(fn(Get $get) => $get('status') == 8)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->native(false),

                FileUpload::make('license_order')
                    ->label(trans('resources.license_order'))
                    ->openable()
                    ->safeDefaults()
                    ->maxSize(5122)
                    ->downloadable()
                    ->visibility('private')
                    ->required()
                    ->visible(fn(Get $get) => $get('status') == 7)
                    ->hidden(fn(Get $get) => $get('status') == 1)
                    ->directory('projects-license-order'),

                FileUpload::make('information_form')
                    ->label(trans('resources.information_form'))
                    ->openable()
                    ->safeDefaults()
                    ->maxSize(5122)
                    ->downloadable()
                    ->visibility('private')
                    ->visible(fn(Get $get) => $get('status') == 7)
                    ->directory('projects-information-form'),

                FileUpload::make('license_certificate')
                    ->label(trans('resources.license_certificate'))
                    ->openable()
                    ->safeDefaults()
                    ->maxSize(5122)
                    ->downloadable()
                    ->visibility('private')
                    ->required()
                    ->visible(fn(Get $get) => $get('status') == 7)
                    ->directory('projects-license-certificate'),


                Section::make([
                    Select::make('bank_guarantee')
                        ->label(trans('resources.bank_guarantee'))
                        ->live()
                        ->options([
                            0 => trans('resources.no'),
                            1 => trans('resources.yes'),
                        ])
                        ->visible(fn(Get $get) => $get('status') == 8),

                    TextInput::make('bank_guarantee_amount')
                        ->numeric()
                        ->visible(fn(Get $get) => $get('bank_guarantee') == 1 && $get('status') == 8)
                        ->label(trans('resources.bank_guarantee_amount')),

                    DatePicker::make('bank_guarantee_date')
                        ->displayFormat('d-m-Y')
                        ->native(false)
                        ->visible(fn(Get $get) => $get('bank_guarantee') == 1 && $get('status') == 8)
                        ->label(trans('resources.bank_guarantee_date')),

                    Section::make([

                        Select::make('financial_bank_capacity')
                            ->label(trans('resources.has_financial_bank_capacity'))
                            ->live()
                            ->options([
                                0 => trans('resources.no'),
                                1 => trans('resources.yes'),
                            ])
                            ->hidden(fn(Get $get) => $get('status') == 8)
                            ->disabled(fn(Get $get) => $get('status') == 8),

                        TextInput::make('project_code')
                            ->numeric()
                            ->visible(fn(Get $get) => $get('status') == 1)
                            ->label(trans('resources.project_code')),

                        FileUpload::make('proceedings_doc')
                            ->label(trans('resources.proceedings_doc'))
                            ->openable()
                            ->safeDefaults()
                            ->maxSize(5122)
                            ->downloadable()
                            ->visibility('private')
                            ->visible(fn(Get $get) => $get('status') == 1)
                            ->directory('projects-proceedings-docs'),

                        FileUpload::make('application_of_investor_doc')
                            ->label(trans('resources.application_of_investor_doc'))
                            ->openable()
                            ->safeDefaults()
                            ->maxSize(5122)
                            ->downloadable()
                            ->visibility('private')
                            ->visible(fn(Get $get) => $get('status') == 1)
                            ->directory('projects-application-of-investor-docs'),

                    ])
                        ->visible(fn(Get $get) => $get('status') == 1)
                        ->columns(),

                ])
                    ->columns(3)

            ])
                ->columns(2),
        ];
    }
}


