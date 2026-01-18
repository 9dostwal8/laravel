<?php

namespace App\Filament\Resources\ProjectResource\Pages\Tabs;

use App\Enums\ProjectActivityAreaTypeEnum;
use App\Models\ActivityArea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ProjectVariants
{
    public static function projectVariants($form)
    {
        return [
            Repeater::make('projectVariants')
                ->relationship()
                ->hiddenLabel()
                ->columnSpanFull()
                ->label(trans('resources.variants.plural'))
                ->columns(3)
                ->schema([
                    Select::make('activity_area_id')
                        ->label(trans('resources.activity-area.single'))
//                        ->getOptionLabelFromRecordUsing(fn ($record) =>self::getTranslation($record->name))
//                        ->relationship('activityArea', 'name')
                            ->options(function () {
                            $data = [];
                            foreach (ActivityArea::pluck('name', 'id')->toArray() as $key => $name) {
                                $data[$key] = self::getTranslation($name);
                            }
                            return $data;
                        })
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
                                    ->afterStateUpdated(function (Request $request, Get $get, Set $set, $state) {
                                        $updatedByCapitalDollar = array_key_exists(
                                            'data.capital_dollar',
                                            current($request->input('components'))['updates']
                                        );
                                        if (! $updatedByCapitalDollar) {
                                            $currencyRate = floatval(str_replace(',', '', $get('currency_rate')));
                                            $state = floatval(str_replace(',', '', $state));
                                            if ($state < 1 || $currencyRate < 1) {
                                                return;
                                            }
                                            $set('capital_dollar', number_format((float) ($state / $currencyRate), 2));
                                        }
                                    })
                                ->numeric()
                                ->reactive()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->debounce(1100)
                                ->required()
                                ->default(0),
                            TextInput::make('capital_dollar')
                                ->label(trans('resources.project.finance.capital_dollar'))
                                        ->afterStateUpdated(function (Request $request, Get $get, Set $set, $state) {
                                            $updatedByCapitalDinar = array_key_exists(
                                                'data.capital_dinar',
                                                current($request->input('components'))['updates']
                                            );
                                            if (! $updatedByCapitalDinar) {
                                                $currencyRate = floatval(str_replace(',', '', $get('currency_rate')));
                                                $state = floatval(str_replace(',', '', $state));

                                                if ($state < 1 || $currencyRate < 1) {
                                                    return;
                                                }
                                                $set('capital_dinar', number_format((float) ($state * $currencyRate), 2));
                                            }
                                        })
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
        ];
    }
}
