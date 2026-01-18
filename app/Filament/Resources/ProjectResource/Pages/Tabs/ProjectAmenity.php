<?php

namespace App\Filament\Resources\ProjectResource\Pages\Tabs;

use App\Enums\AmenityStatus;
use App\Models\Amenity;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

trait ProjectAmenity
{
    public static function projectAmenity($form)
    {
        return [
            Repeater::make('amenities')
                ->relationship()
                ->columnSpanFull()
                ->label(trans('resources.amenity.plural'))
                ->schema([

                    Section::make([
                        Select::make('amenity_id')
                            ->label(trans('resources.amenity.single'))
                            ->relationship('amenity', 'name', function ($get, $query) {
                                $data = [];
                                $variants = $get('../../projectVariants');
                                foreach ($variants as $variant) {
                                    $data[] = $variant['activity_area_id'];
                                }
                                return $query->whereIn('activity_area_id', $data);
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
//                            ->options(function (Get $get) {
//                                $options = [];
//                                $variants = $get('../../projectVariants');
//                                foreach ($variants as $variant) {
//                                    $activity_areas = Amenity::where('activity_area_id', $variant['activity_area_id'])->get();
//
//                                    foreach ($activity_areas as $activity_area) {
//                                        $options[$activity_area->id] = self::getTranslation($activity_area->name);
//                                    }
//                                }
//                                return $options;
//                            })
                            ->preload()
                            ->searchable(['name->ckb', 'name->en'])
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
                          //  ->distinct()
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
        ];
    }
}
