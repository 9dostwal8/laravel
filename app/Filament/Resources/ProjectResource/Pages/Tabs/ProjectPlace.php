<?php

namespace App\Filament\Resources\ProjectResource\Pages\Tabs;

use App\Enums\LandAllocationTypeEnum;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

trait ProjectPlace
{
    public static function projectPlace($form)
    {
        return [

            Select::make('state_id')
                ->label(trans('resources.state.single'))
                ->relationship('state', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                ->searchable(['name->ckb', 'name->en'])
                ->preload()
                ->live()
                ->required(),

            Select::make('department_id')
                ->label(trans('resources.department.single'))
                ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                ->relationship('department', 'name', function (Builder $query, Get $get) {
                    $query->where('state_id', $get('state_id'));
                })
                ->searchable(['name->ckb', 'name->en'])
                ->preload()
                ->required()
                ->live(),

            Select::make('area_id')
                ->label(trans('resources.area.single'))
                ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                ->relationship('area', 'name', function (Builder $query, Get $get) {
                    $query->where('department_id', $get('department_id'));
                })
                ->searchable(['name->ckb', 'name->en'])
                ->preload(),

            TextInput::make('village')
                ->label(trans('resources.project.village'))
                ->maxLength(255),

            TextInput::make('project_location')
                ->label(trans('resources.project.project_location'))
                ->maxLength(255),

            Repeater::make('land_number')
                ->label(trans('resources.project.land_number'))
                ->simple(
                    Textarea::make('number'),
                )
                //->disabled(fn(Get $get) => $get('status') == 8),
        ,

            Select::make('type_of_land_allocation')
                ->label(trans('resources.project.type_of_land_allocation'))
                ->options(LandAllocationTypeEnum::class)
//                ->searchable(fn(Get $get) => $get('status') != 8)
//                ->disabled(fn(Get $get) => $get('status') == 8)
                ->live(),

            Select::make('land_granting_organization')
                ->label(trans('resources.project.land_granting_organization'))
                ->options(trans('resources.ministries'))
              //  ->relationship('organization', 'name')
              //  ->getOptionLabelFromRecordUsing(fn ($record) => self::getTranslation($record->name))
                ->searchable(fn(Get $get) => $get('status') != 8 ? ['name->ckb', 'name->en'] : false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->preload(),

            // type_of_land_allocation => 2

            TextInput::make('land_allocation_number')
                ->label(trans('resources.land_allocation_number'))
               // ->visible(fn(Get $get) => $get('type_of_land_allocation') == 2)
            ,

            DatePicker::make('land_allocation_date')
                ->native(false)
                ->label(trans('resources.land_allocation_date'))
                ->displayFormat('d-m-Y')
                ->disabled(fn(Get $get) => $get('status') == 8)
              //  ->visible(fn(Get $get) => $get('type_of_land_allocation') == 2)
            ,

            // type_of_land_allocation => 2

            TextInput::make('full_address')
                ->label(trans('resources.project.full_address'))
                ->maxLength(255)
                ->hint(trans('resources.project.full_address_hint')),


            // type_of_land_allocation == 2
            FileUpload::make('attachment_of_decision')
                ->label(trans('resources.attachment_of_decision'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                ->visibility('private')
              //  ->visible(fn(Get $get) => $get('type_of_land_allocation') == 2)
                ->directory('projects-license-order'),

//            GoogleMaps::make('location')
//                ->label(trans('resources.project.location'))
//                ->autocomplete('full_address')
//                ->columnSpanFull()
//                ->autocompleteReverse()
//                ->clickable()
//                ->defaultLocation([
//                    35.5211236,
//                    43.784937
//                ])
//                ->mapControls([
//                    'mapTypeControl'    => false,
//                    'scaleControl'      => true,
//                    'streetViewControl' => false,
//                    'rotateControl'     => false,
//                    'fullscreenControl' => true,
//                    'searchBoxControl'  => false,
//                    'zoomControl'       => true,
//                ])
//                ->afterStateUpdated(function (Set $set, $state) {
//                    $set('lat', $state['lat']);
//                    $set('lng', $state['lng']);
//                }),

            Map::make('location')
                ->label(function ($state) {
                    $label = trans('resources.project.location');
                    if (isset($state['lat']) && isset($state['lng'])) {
                        $link = "https://www.google.com/maps?q={$state['lat']},{$state['lng']}";
                        return new HtmlString("{$label} <a href='{$link}' target='_blank'>" . trans('resources.show_on_google_map') . "</a>");
                    } else {
                        return $label;
                    }
                })
                ->columnSpanFull()
                ->zoom(7)
                ->defaultLocation(35.5211236, 43.784937)
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('lat', $state['lat']);
                    $set('lng', $state['lng']);
                })
                ->afterStateHydrated(function ($state, $record, Set $set, Get $get, $context, $component): void {
                    if ($context == 'view') {
                        $component->draggable(false);
                    }
                    $set('location', ['lat' => $get('lat'), 'lng' => $get('lng')]);
                })
                ->liveLocation()
                ->showMarker()
                ->markerColor("#22c55eff")
                ->showFullscreenControl()
                ->showZoomControl()
                ->draggable()
                ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                ->detectRetina()
                ->showMyLocationButton()
                ->extraTileControl([])
                ->extraControl([
                    'zoomDelta'           => 1,
                    'zoomSnap'            => 2,
                ])
                ->extraStyles([
                    'min-height: 400px',
                ])
             //   ->label(trans('resources.project.location'))
            ,

            TextInput::make('lat')
                ->label(trans('resources.latitude'))
                ->numeric(),

            TextInput::make('lng')
                ->label(trans('resources.longitude'))
                ->numeric(),

        ];
    }
}
