<?php

namespace App\Filament\Pages;

use App\Enums\InvestmentTypeEnum;
use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\Reports\ReportByActivityAreaCityResource;
use App\Filament\Resources\Reports\ReportByActivityAreaResource;
use App\Filament\Resources\Reports\ReportByActivityAreaByJointResource;
use App\Filament\Resources\Reports\ReportByActivityAreaYearResource;
use App\Filament\Resources\Reports\ReportByCityCapitalResource;
use App\Filament\Resources\Reports\ReportByInvestmentTypeCountryResource;
use App\Filament\Resources\Reports\ReportByInvestmentTypeResource;
use App\Filament\Resources\Reports\ReportByWorkforceCityResource;
use App\Filament\Resources\Reports\ReportByWorkforceSectorResource;
use App\Filament\Resources\Reports\ReportByYearCapitalResource;
use App\Filament\Resources\Reports\ReportByProjectUnitCityResidencyResource;
use App\Filament\Resources\Reports\ReportByProjectUnitStatusCityResidencyResource;
use App\Filament\Resources\Reports\ReportByServiceUnitsCityResidencyResource;
use App\Models\ActivityArea;
use App\Models\Country;
use App\Models\Area;
use App\Models\Department;
use App\Models\LicensingAuthority;
use App\Models\Organization;
use App\Models\Status;
use App\Traits\LangSwitcher;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;

class ReportForm extends Page implements HasForms
{
    use InteractsWithForms, LangSwitcher, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report-form';
    protected static ?int $navigationSort = 10;

    public static array $reportTypes = [];

    public ?array $data = [];

    public function __construct()
    {
        static::$reportTypes = [
            'investment-by-sectors' => trans('resources.capital_by_sector'),
            'governorate_investment_type' => trans('resources.governorate_investment_type'),
            'report-by-year-capitals' => trans('resources.year_capital_In_million_dollar'),
            'project_area_in_donums_by_sector' => trans('resources.project_area_in_donums_by_sector'),
            'investment_type_governorate' =>  trans('resources.investment_type_governorate'),
            'projects_costs' => trans('resources.projects_costs'),
            'capital_by_month' => trans('resources.capital_by_month'),
            'investor_type_country' => trans('resources.investor_type_country'),
            'r_investment_type' => trans('resources.r_investment_type'),
            'capital_by_year' => trans('resources.capital_by_year'),
            'sector_capital_area' => trans('resources.sector_capital_area'),
            'projects_area' => trans('resources.projects_area'),
            'sector_governorate' => trans('resources.sector_governorate'),
            'years_capital_in_million_dollar_by_investment_type' => trans('resources.years_capital_in_million_dollar_by_investment_type'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.reports_group_menu_name');
    }

    public function getTitle(): string|Htmlable
    {
        return trans('panel.report_form');
    }

    public static function getNavigationLabel(): string
    {
        return trans('panel.report_form');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([

                    Select::make('status')
                        ->label(trans('resources.project.status'))
                        ->options(ProjectStatus::class)
                        ->live()
                        ->searchable(),

                    Select::make('project_state')
                        ->label(trans('resources.project.state'))
                        ->options(function (Get $get) {

                            if (empty($get('status'))) return [];

                            $data = [];
                            $check = Status::where('type', ProjectResource::class)
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
                        ->preload(),

                    DatePicker::make('from')
                        ->label(trans('resources.from_date'))
                        ->native(false)
                        ->format('Y/m/d')
                        ->displayFormat('Y/m/d')
                        ->default('2006/8/1')
                        ->required(),

                    DatePicker::make('to')
                        ->label(trans('resources.to_date'))
                        ->native(false)
                        ->format('Y/m/d')
                        ->displayFormat('Y/m/d')
                        ->default(now()->format('Y/m/d'))
                        ->required(),

                    Select::make('activity_area_id')
                        ->label(trans('resources.activity-area.single'))
                        ->options(function () {
                            $data = [];
                            foreach (ActivityArea::pluck('name', 'id')->toArray() as $key => $name) {
                                if (empty(get_user_sectors()) || in_array($key, get_user_sectors())) {
                                    $data[$key] = self::getTranslation($name);
                                }
                            }
                            return $data;
                        })
                        ->searchable(['name->ckb', 'name->en'])
                        ->live()
                        ->preload(),

                    Select::make('organization_id')
                        ->label(trans('resources.organization.single'))
                        ->options(function () {
                            $data = [];
                            foreach (Organization::pluck('name', 'id')->toArray() as $key => $name) {
                                $data[$key] = self::getTranslation($name);
                            }
                            return $data;
                        })
                        ->searchable(['name->ckb', 'name->en'])
                        ->preload(),

                    Select::make('department_id')
                        ->label(trans('resources.department.single'))
                        ->options(function () {
                            $data = [];
                            foreach (Department::pluck('name', 'id')->toArray() as $key => $name) {
                                $data[$key] = self::getTranslation($name);
                            }
                            return $data;
                        })
                        ->searchable(['name->ckb', 'name->en'])
                        ->preload(),

                    Select::make('area_id')
                        ->label(trans('resources.area.single'))
                        ->options(function () {
                            $data = [];
                            foreach (Area::pluck('name', 'id')->toArray() as $key => $name) {
                                $data[$key] = self::getTranslation($name);
                            }
                            return $data;
                        })
                        ->searchable(['name->ckb', 'name->en'])
                        ->preload(),

                    TextInput::make('village')
                        ->label(trans('resources.project.village')),

                    Select::make('investment_type')
                        ->label(trans('resources.project.investment_type'))
                        // ->required(function (Get $get) {
                        //     if ($get('type') == 'type_of_user_country_number_of_projects_capital_area') {
                        //         return true;
                        //     }

                        //     return false;
                        // })
                        ->live()
                        ->options(InvestmentTypeEnum::class),

                    Select::make('country_id')
                        ->visible(fn(Get $get) => $get('investment_type') == InvestmentTypeEnum::FOREIGN->value || $get('investment_type') == InvestmentTypeEnum::JOINT->value)
                        ->label(trans('resources.investment_country'))
                        ->searchable()
                        ->options(function () {
                            $data = [];
                            foreach (Country::all() as $item) {
                                $data[$item->id] = self::getTranslation($item->name);
                            }
                            return $data;
                        })
                        ->live()
                        ->preload(),

                    Select::make('licensing_authority_id')
                        ->label(trans('resources.licensing-authorities.plural'))
                        ->options(function () {
                            $data = [];
                            foreach (LicensingAuthority::all() as $item) {
                                $data[$item->id] = self::getTranslation($item->name);
                            }
                            return $data;
                        })
                        ->preload(),

                    TextInput::make('progress_percentage_from')
                        ->numeric()
                        ->label(trans('resources.progress.percentage') . ' - ' . trans('resources.from')),

                    TextInput::make('progress_percentage_to')
                        ->numeric()
                        ->label(trans('resources.progress.percentage') . ' - ' . trans('resources.to')),

                    // New
//                    DatePicker::make('cancellation_date_from')
//                        ->label(trans('resources.cancellation_date'). ' - ' . trans('resources.from'))
//                        ->native(false)
//                        ->format('Y/m/d')
//                        ->displayFormat('Y/m/d'),
//
//                    DatePicker::make('cancellation_date_to')
//                        ->label(trans('resources.cancellation_date'). ' - ' . trans('resources.to'))
//                        ->native(false)
//                        ->format('Y/m/d')
//                        ->displayFormat('Y/m/d'),
                    // New

                    DatePicker::make('first_customs_date_from')
                        ->label(trans('resources.first_customs_date'). ' - ' . trans('resources.from'))
                        ->native(false)
                        ->format('Y/m/d')
                        ->displayFormat('Y/m/d'),

                    DatePicker::make('last_customs_date_to')
                        ->label(trans('resources.first_customs_date'). ' - ' . trans('resources.to'))
                        ->native(false)
                        ->format('Y/m/d')
                        ->displayFormat('Y/m/d'),

                    Radio::make('type')
                        ->label(trans('resources.type'))
                        ->options(fn() => trans('resources.the_utilization_based_on_the'))
                        ->columnSpanFull()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $autoSetTypes = [
                                'project_unit_city_residency',
                                'project_unit_status_city_residency',
                                'service_units_city_residency'
                            ];
                            
                            if (in_array($state, $autoSetTypes)) {
                                $set('activity_area_id', 1);
                            }
                        })
                        ->columns(),

                ])
                    ->columns(5),

            ])
            ->statePath('data');
    }

    public function submit()
    {
        $type = $this->form->getState()['type'];
        $params = '?' . http_build_query($this->form->getState());
        $url = null;

        switch ($type) {
            case 'city_number_of_projects_capital':
                $url = ReportByCityCapitalResource::getNavigationUrl(); break;
            case 'sector_number_of_projects_capital_area':
                $url = ReportByActivityAreaResource::getNavigationUrl(); break;
            case 'sector_number_of_projects_capital_area_joint':
                $url = ReportByActivityAreaByJointResource::getNavigationUrl(); break;
            case 'sector_province_number_of_projects_capital_area':
                $url = ReportByActivityAreaCityResource::getNavigationUrl(); break;
            case 'type_of_user_number_of_projects_capital':
                $url = ReportByInvestmentTypeResource::getNavigationUrl(); break;
            case 'type_of_user_country_number_of_projects_capital_area':
                $url = ReportByInvestmentTypeCountryResource::getNavigationUrl(); break;
            case 'the_utilization_based_on_the_year':
                $url = ReportByYearCapitalResource::getNavigationUrl(); break;
            case 'sector_year_number_of_projects_capital_area':
                $url = ReportByActivityAreaYearResource::getNavigationUrl(); break;
            case 'workforce_city':
                $url = ReportByWorkforceCityResource::getNavigationUrl(); break;
            case 'workforce_sector':
                $url = ReportByWorkforceSectorResource::getNavigationUrl(); break;
            case 'project_unit_city_residency':
                $url = ReportByProjectUnitCityResidencyResource::getNavigationUrl(); break;
            case 'project_unit_status_city_residency':
                $url = ReportByProjectUnitStatusCityResidencyResource::getNavigationUrl(); break;
            case 'service_units_city_residency':
                $url = ReportByServiceUnitsCityResidencyResource::getNavigationUrl(); break;
        }

        if (empty($url)) {
            return null;
        }

        return redirect($url . $params);
    }

    protected function getActions(): array
    {
        return [
            Action::make('save-data')
                ->label(trans('resources.get_data'))
                ->action('submit')
        ];
    }
}
