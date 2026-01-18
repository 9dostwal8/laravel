<?php

namespace App\Filament\Resources\ProjectResource\Pages\Tabs;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Carbon;

trait ProjectWorkInformation
{
    public static function projectWorkInformation($form)
    {
        return [
            Section::make(trans('resources.project.fixed_workforce_number.title'))
                ->description(trans('resources.project.fixed_workforce_number.description'))
                ->compact()
                ->schema([
                    TextInput::make('total_permanent_working_group')
                        ->label(trans('resources.project.fixed_workforce_number.total_permanent_working_group'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('kurdistan_fixed_workforce_count')
                        ->label(trans('resources.project.fixed_workforce_number.kurdistan'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('foreign_fixed_workforce_count')
                        ->label(trans('resources.project.fixed_workforce_number.foreign'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('iraq_fixed_workforce_count')
                        ->label(trans('resources.project.fixed_workforce_number.iraq'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('seperated_areas_fixed_workforce_count')
                        ->label(trans('resources.project.fixed_workforce_number.seperated_areas'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                ])->columns(4),
            Section::make(trans('resources.project.temporary_workforce_number.title'))
                ->description(trans('resources.project.temporary_workforce_number.description'))
                ->compact()
                ->schema([
                    TextInput::make('total_temporary_labor')
                        ->label(trans('resources.project.temporary_workforce_number.total_temporary_labor'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),

                    TextInput::make('kurdistan_temporary_workforce_count')
                        ->label(trans('resources.project.temporary_workforce_number.kurdistan'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('foreign_temporary_workforce_count')
                        ->label(trans('resources.project.temporary_workforce_number.foreign'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('iraq_temporary_workforce_count')
                        ->label(trans('resources.project.temporary_workforce_number.iraq'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                    TextInput::make('seperated_areas_temporary_workforce_count')
                        ->label(trans('resources.project.temporary_workforce_number.seperated_areas'))
                        ->minLength(0)
                        ->maxLength(500)
                        ->numeric(),
                ])->columns(4),

            DatePicker::make('land_delivered_at')
              //  ->beforeOrEqual('licence_received_at')
              //  ->after('requested_at')
                ->label(trans('resources.project.land_delivered_at'))
                ->displayFormat('d-m-Y')
                ->native(false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->nullable(),

            DatePicker::make('licence_received_at')
              //  ->beforeOrEqual('started_at')
              //  ->after('land_delivered_at')
                ->label(trans('resources.project.licence_received_at'))
                ->displayFormat('d-m-Y')
                ->native(false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->nullable(),
            DatePicker::make('started_at')
               // ->beforeOrEqual('estimated_project_end_date')
              //  ->after('land_delivered_at')
                ->label(trans('resources.project.started_at'))
                ->displayFormat('d-m-Y')
                ->native(false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->afterStateHydrated(fn(Set $set, Get $get, $state) => static::estimateDatePlusStartDate($set, $get, $state))
                ->afterStateUpdated(fn(Set $set, Get $get, $state) => static::estimateDatePlusStartDate($set, $get, $state))
                ->nullable(),
            DatePicker::make('estimated_project_end_date')
              //  ->beforeOrEqual('actual_project_end_date')
              //  ->after('started_at')
                ->label(trans('resources.project.estimated_project_end_date'))
                ->displayFormat('d-m-Y')
                ->native(false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->nullable(),
            DatePicker::make('actual_project_end_date')
               // ->after('estimated_project_end_date')
                ->label(trans('resources.project.actual_project_end_date'))
                ->displayFormat('d-m-Y')
                ->native(false)
                ->disabled(fn(Get $get) => $get('status') == 8)
                ->nullable(),

            TextInput::make('performance_rate')
                ->label(trans('resources.performance_rate'))
                ->disabled(),
        ];
    }

    private static function estimateDatePlusStartDate(Set $set, Get $get, $state) {

        // Estimate date
        $est_year = $get('execution_time_years');
        $est_month = $get('execution_time_months');
        $est_day = $get('execution_time_days');

        if (! empty($est_year) && ! empty($est_month) && ! empty($est_day) && ! empty($state)) {
            $end_date = Carbon::make($state)
                ->addYears($est_year)
                ->addMonths($est_month)
                ->addDays($est_day);

            $set('estimated_project_end_date', $end_date);
        }

    }
}
