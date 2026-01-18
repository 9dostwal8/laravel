@php
    use App\Traits\LangSwitcher;

    $data = [];
    $total_projects = 0;
    $total_daycare = 0;
    $total_kindergarten = 0;
    $total_school = 0;
    $total_health = 0;
    $total_police = 0;
    $total_water = 0;
    $total_all_units = 0;

    $grouped_records = $records->groupBy('state_id');

    // First calculate all totals
    foreach ($grouped_records as $state_id => $state_records) {
        $projects_count = $state_records->count();
        $total_projects += $projects_count;
        
        $daycare = $state_records->sum('daycare_units');
        $total_daycare += $daycare;
        
        $kindergarten = $state_records->sum('kindergarten_units');
        $total_kindergarten += $kindergarten;
        
        $school = $state_records->sum('school_units');
        $total_school += $school;
        
        $health = $state_records->sum('health_center_units');
        $total_health += $health;
        
        $police = $state_records->sum('police_station_units');
        $total_police += $police;
        
        $water = $state_records->sum('water_treatment_units');
        $total_water += $water;

        $total_units = $daycare + $kindergarten + $school + $health + $police + $water;
        $total_all_units += $total_units;

        $state = $state_records->first()->state;
        $state_name = $state ? ($state->name[app()->getLocale()] ?? array_values($state->name)[0] ?? 'resources.unknown') : 'resources.unknown';

        $data[] = [
            'state' => $state_name,
            'projects_count' => $projects_count,
            'daycare' => $daycare,
            'kindergarten' => $kindergarten,
            'school' => $school,
            'health' => $health,
            'police' => $police,
            'water' => $water,
            'total_units' => $total_units,
        ];
    }

    // Now calculate all percentages after we have the totals
    foreach ($data as $key => $row) {
        $data[$key]['projects_percentage'] = $total_projects ? ($row['projects_count'] / $total_projects) * 100 : 0;
        $data[$key]['daycare_percentage'] = $total_daycare ? ($row['daycare'] / $total_daycare) * 100 : 0;
        $data[$key]['kindergarten_percentage'] = $total_kindergarten ? ($row['kindergarten'] / $total_kindergarten) * 100 : 0;
        $data[$key]['school_percentage'] = $total_school ? ($row['school'] / $total_school) * 100 : 0;
        $data[$key]['health_percentage'] = $total_health ? ($row['health'] / $total_health) * 100 : 0;
        $data[$key]['police_percentage'] = $total_police ? ($row['police'] / $total_police) * 100 : 0;
        $data[$key]['water_percentage'] = $total_water ? ($row['water'] / $total_water) * 100 : 0;
        $data[$key]['total_units_percentage'] = $total_all_units ? ($row['total_units'] / $total_all_units) * 100 : 0;
    }

    // Calculate total percentages
    $total_projects_percentage = 100;
    $total_daycare_percentage = 100;
    $total_kindergarten_percentage = 100;
    $total_school_percentage = 100;
    $total_health_percentage = 100;
    $total_police_percentage = 100;
    $total_water_percentage = 100;
    $total_units_percentage = 100;

    // Format all percentages to 2 decimal places
    foreach ($data as $key => $row) {
        $data[$key]['projects_percentage'] = number_format($row['projects_percentage'], 2);
        $data[$key]['daycare_percentage'] = number_format($row['daycare_percentage'], 2);
        $data[$key]['kindergarten_percentage'] = number_format($row['kindergarten_percentage'], 2);
        $data[$key]['school_percentage'] = number_format($row['school_percentage'], 2);
        $data[$key]['health_percentage'] = number_format($row['health_percentage'], 2);
        $data[$key]['police_percentage'] = number_format($row['police_percentage'], 2);
        $data[$key]['water_percentage'] = number_format($row['water_percentage'], 2);
        $data[$key]['total_units_percentage'] = number_format($row['total_units_percentage'], 2);
    }
@endphp

<div style="direction: rtl" dir="rtl" class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.province') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.project_count') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.project_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.nursery') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.nursery_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.kindergarten') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.kindergarten_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.school') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.school_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.health_center') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.health_center_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.police_station') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.police_station_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.water_treatment_station') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.service_units.water_treatment_station_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.total') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.total_percentage') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            @foreach ($data as $row)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['state'] }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['projects_count']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['projects_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['daycare']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['daycare_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['kindergarten']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['kindergarten_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['school']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['school_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['health']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['health_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['police']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['police_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['water']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['water_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['total_units']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['total_units_percentage'] }}%
                    </td>
                </tr>
            @endforeach
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    کۆی گشتی
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_projects) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_projects_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_daycare) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_daycare_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_kindergarten) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_kindergarten_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_school) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_school_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_health) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_health_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_police) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_police_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_water) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_water_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_all_units) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_units_percentage, 2) }}%
                </td>
            </tr>
        </tbody>
    </table>
</div> 