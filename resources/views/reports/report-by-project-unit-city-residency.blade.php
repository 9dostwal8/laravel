@php
    // Check if records exist
    if (!$records || $records->isEmpty()) {
        echo '<div class="text-center p-4">' . trans('resources.no_data_available') . '</div>';
        return;
    }

    $data = [];
    $total_projects = 0;
    $total_loan_fund = 0;
    $total_non_loan_fund = 0;
    $total_house = 0;
    $total_apartment = 0;
    $total_villa = 0;
    $total_all_units = 0;

    $grouped_records = $records->groupBy('state_id');

    // First calculate all totals
    foreach ($grouped_records as $state_id => $state_records) {
        $projects_count = $state_records->count();
        $total_projects += $projects_count;
        
        $loan_fund = $state_records->sum(function($project) {
            return $project->projectVariants ? $project->projectVariants->sum('loan_fund') : 0;
        });
        $total_loan_fund += $loan_fund;
        
        $non_loan_fund = $state_records->sum(function($project) {
            return $project->projectVariants ? $project->projectVariants->sum('non_loan_fund') : 0;
        });
        $total_non_loan_fund += $non_loan_fund;
        
        $house = $state_records->sum(function($project) {
            return $project->amenities ? $project->amenities->where('amenity_id', 13)->sum('counts') : 0;
        });
        $total_house += $house;
        
        $apartment = $state_records->sum(function($project) {
            return $project->amenities ? $project->amenities->where('amenity_id', 132)->sum('counts') : 0;
        });
        $total_apartment += $apartment;
        
        $villa = $state_records->sum(function($project) {
            return $project->amenities ? $project->amenities->where('amenity_id', 2)->sum('counts') : 0;
        });
        $total_villa += $villa;

        $total_units = $house + $apartment + $villa;
        $total_all_units += $total_units;

        $state = $state_records->first()->state;
        $state_name = 'resources.unknown';
        if ($state && isset($state->name)) {
            if (is_array($state->name)) {
                $state_name = $state->name[app()->getLocale()] ?? array_values($state->name)[0] ?? 'resources.unknown';
            } else {
                $state_name = $state->name;
            }
        }

        $data[] = [
            'state' => $state_name,
            'projects_count' => $projects_count,
            'loan_fund' => $loan_fund,
            'non_loan_fund' => $non_loan_fund,
            'house' => $house,
            'apartment' => $apartment,
            'villa' => $villa,
            'total_units' => $total_units,
        ];
    }

    // Now calculate all percentages after we have the totals
    foreach ($data as $key => $row) {
        $data[$key]['projects_percentage'] = $total_projects ? ($row['projects_count'] / $total_projects) * 100 : 0;
        $data[$key]['loan_fund_percentage'] = $total_loan_fund ? ($row['loan_fund'] / $total_loan_fund) * 100 : 0;
        $data[$key]['non_loan_fund_percentage'] = $total_non_loan_fund ? ($row['non_loan_fund'] / $total_non_loan_fund) * 100 : 0;
        $data[$key]['house_percentage'] = $total_house ? ($row['house'] / $total_house) * 100 : 0;
        $data[$key]['apartment_percentage'] = $total_apartment ? ($row['apartment'] / $total_apartment) * 100 : 0;
        $data[$key]['villa_percentage'] = $total_villa ? ($row['villa'] / $total_villa) * 100 : 0;
        $data[$key]['total_units_percentage'] = $total_all_units ? ($row['total_units'] / $total_all_units) * 100 : 0;
    }

    // Calculate total percentages
    $total_projects_percentage = 100;
    $total_loan_fund_percentage = 100;
    $total_non_loan_fund_percentage = 100;
    $total_house_percentage = 100;
    $total_apartment_percentage = 100;
    $total_villa_percentage = 100;
    $total_units_percentage = 100;

    // Format all percentages to 2 decimal places
    foreach ($data as $key => $row) {
        $data[$key]['projects_percentage'] = number_format($row['projects_percentage'], 2);
        $data[$key]['loan_fund_percentage'] = number_format($row['loan_fund_percentage'], 2);
        $data[$key]['non_loan_fund_percentage'] = number_format($row['non_loan_fund_percentage'], 2);
        $data[$key]['house_percentage'] = number_format($row['house_percentage'], 2);
        $data[$key]['apartment_percentage'] = number_format($row['apartment_percentage'], 2);
        $data[$key]['villa_percentage'] = number_format($row['villa_percentage'], 2);
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
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.loan_fund') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.loan_fund_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.non_loan_fund') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.non_loan_fund_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.house') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.house_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.apartment') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.apartment_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.villa') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.villa_percentage') }}</th>
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
                        {{ number_format($row['loan_fund']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['loan_fund_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['non_loan_fund']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['non_loan_fund_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['house']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['house_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['apartment']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['apartment_percentage'] }}%
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['villa']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['villa_percentage'] }}%
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
                    {{ number_format($total_loan_fund) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_loan_fund_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_non_loan_fund) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_non_loan_fund_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_house) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_house_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_apartment) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_apartment_percentage, 2) }}%
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_villa) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_villa_percentage, 2) }}%
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