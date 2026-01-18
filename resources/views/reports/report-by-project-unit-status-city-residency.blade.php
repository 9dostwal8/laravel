@php
    use App\Traits\LangSwitcher;

    $data = [];
    $total_completed_units = 0;
    $total_incomplete_units = 0;
    $total_stopped_units = 0;
    $total_not_started_units = 0;
    $total_high_price = 0;
    $total_medium_price = 0;
    $total_low_price = 0;
    $total_all_units = 0;

    $grouped_records = $records->groupBy('state_id');

    // First calculate all totals
    foreach ($grouped_records as $state_id => $state_records) {
        // Status counts
        $completed_units = $state_records->sum('completed_units');
        $total_completed_units += $completed_units;

        $incomplete_units = $state_records->sum('incomplete_units');
        $total_incomplete_units += $incomplete_units;

        $stopped_units = $state_records->sum('stopped_units');
        $total_stopped_units += $stopped_units;

        $not_started_units = $state_records->sum('not_started_units');
        $total_not_started_units += $not_started_units;

        // Price ranking counts
        $high_price = $state_records->sum('high_price_units');
        $total_high_price += $high_price;

        $medium_price = $state_records->sum('medium_price_units');
        $total_medium_price += $medium_price;

        $low_price = $state_records->sum('low_price_units');
        $total_low_price += $low_price;

        // Calculate total units for this state
        $total_units = $state_records->sum('total_units');
        $total_all_units += $total_units;

        $state = $state_records->first()->state;
        $state_name = $state ? ($state->name[app()->getLocale()] ?? array_values($state->name)[0] ?? 'resources.unknown') : 'resources.unknown';

        $data[] = [
            'state' => $state_name,
            'completed_units' => $completed_units,
            'incomplete_units' => $incomplete_units,
            'stopped_units' => $stopped_units,
            'not_started_units' => $not_started_units,
            'high_price' => $high_price,
            'medium_price' => $medium_price,
            'low_price' => $low_price,
            'total_units' => $total_units,
        ];
    }

    // Now calculate all percentages
    foreach ($data as $key => $row) {
        $data[$key]['completed_percentage'] = $total_completed_units ? ($row['completed_units'] / $total_completed_units) * 100 : 0;
        $data[$key]['incomplete_percentage'] = $total_incomplete_units ? ($row['incomplete_units'] / $total_incomplete_units) * 100 : 0;
        $data[$key]['stopped_percentage'] = $total_stopped_units ? ($row['stopped_units'] / $total_stopped_units) * 100 : 0;
        $data[$key]['not_started_percentage'] = $total_not_started_units ? ($row['not_started_units'] / $total_not_started_units) * 100 : 0;
        $data[$key]['high_price_percentage'] = $total_high_price ? ($row['high_price'] / $total_high_price) * 100 : 0;
        $data[$key]['medium_price_percentage'] = $total_medium_price ? ($row['medium_price'] / $total_medium_price) * 100 : 0;
        $data[$key]['low_price_percentage'] = $total_low_price ? ($row['low_price'] / $total_low_price) * 100 : 0;
        $data[$key]['total_units_percentage'] = $total_all_units ? ($row['total_units'] / $total_all_units) * 100 : 0;
    }

    // Set total percentages to 100
    $total_completed_percentage = 100;
    $total_incomplete_percentage = 100;
    $total_stopped_percentage = 100;
    $total_not_started_percentage = 100;
    $total_high_price_percentage = 100;
    $total_medium_price_percentage = 100;
    $total_low_price_percentage = 100;
    $total_units_percentage = 100;

    // Format all percentages to 2 decimal places
    foreach ($data as $key => $row) {
        $data[$key]['completed_percentage'] = number_format($row['completed_percentage'], 2);
        $data[$key]['incomplete_percentage'] = number_format($row['incomplete_percentage'], 2);
        $data[$key]['stopped_percentage'] = number_format($row['stopped_percentage'], 2);
        $data[$key]['not_started_percentage'] = number_format($row['not_started_percentage'], 2);
        $data[$key]['high_price_percentage'] = number_format($row['high_price_percentage'], 2);
        $data[$key]['medium_price_percentage'] = number_format($row['medium_price_percentage'], 2);
        $data[$key]['low_price_percentage'] = number_format($row['low_price_percentage'], 2);
        $data[$key]['total_units_percentage'] = number_format($row['total_units_percentage'], 2);
    }
@endphp

<div style="direction: rtl" dir="rtl" class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.province') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.completed') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.incomplete') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.stopped') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.not_started') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.high_price') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.medium_price') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.low_price') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
                
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project_units.total') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.reports_shared.percentage') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            @foreach ($data as $row)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['state'] }}
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['completed_units']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['completed_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['incomplete_units']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['incomplete_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['stopped_units']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['stopped_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['not_started_units']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['not_started_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['high_price']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['high_price_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['medium_price']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['medium_price_percentage'] }}%
                    </td>
                    
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($row['low_price']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $row['low_price_percentage'] }}%
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
                    {{ number_format($total_completed_units) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_completed_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_incomplete_units) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_incomplete_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_stopped_units) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_stopped_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_not_started_units) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_not_started_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_high_price) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_high_price_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_medium_price) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_medium_price_percentage, 2) }}%
                </td>
                
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_low_price) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($total_low_price_percentage, 2) }}%
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