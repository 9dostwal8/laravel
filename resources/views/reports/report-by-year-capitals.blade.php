@php
    // Additional filtering based on request parameters
    $filteredRecords = $records;
    
    // If date filters are set, filter the collection by year based on status
    if (request('from') && request('to')) {
        $fromYear = date('Y', strtotime(request('from')));
        $toYear = date('Y', strtotime(request('to')));
        $status = (int) request('status');
        
        $filteredRecords = $records->filter(function ($record) use ($fromYear, $toYear, $status) {
            // Select the appropriate year field based on status
            switch ($status) {
                case 1:
                case 8:
                    $recordYear = $record->requested_year;
                    break;
                case 7:
                    $recordYear = $record->licence_received_year;
                    break;
                case 5:
                    $recordYear = $record->cancellation_year;
                    break;
                default:
                    // Default: check both requested_year and licence_received_year
                    $requestedYear = $record->requested_year;
                    $licenceYear = $record->licence_received_year;
                    
                    return ($requestedYear >= $fromYear && $requestedYear <= $toYear) ||
                           ($licenceYear >= $fromYear && $licenceYear <= $toYear);
            }
            
            return $recordYear && $recordYear >= $fromYear && $recordYear <= $toYear;
        });
    }

    
    $data = [];
    $grand_count = 0;
    $projects_capital_dollar_grand = 0;
    $projects_hectare_area_grand = 0;
    $totals = [
        "count" => 0,
        "count_percent" => 0,
        "dollar" => 0,
        "dollar_percent" => 0,
        "hectare_area" => 0,
        "hectare_area_percent" => 0
    ];
    
    // Group by the appropriate year field based on status
    $status = (int) request('status');
    $groupByField = 'requested_year'; // default
    
    switch ($status) {
        case 7:
            $groupByField = 'licence_received_year';
            break;
        case 5:
            $groupByField = 'cancellation_year';
            break;
        case 1:
        case 8:
        default:
            $groupByField = 'requested_year';
            break;
    }
    
    foreach ($filteredRecords->groupBy($groupByField) as $year => $projects) {
        $year_count = $projects->count();
        $project_capital_dollar = 0;
        $projects_hectare_area = 0;

        foreach ($projects as $project) {
            foreach ($project->projectVariants as $variant) {
                $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
            }
            $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
        }

        $grand_count += $year_count;
        $projects_capital_dollar_grand += $project_capital_dollar;
        $projects_hectare_area_grand += $projects_hectare_area;

        $data[$year] = [
            'count' => $year_count,
            'dollar' => $project_capital_dollar,
            'hectare_area' => $projects_hectare_area
        ];
    }

    foreach ($data as $year => &$values) {
        $values['count_percent'] = ($values['count'] * 100) / $grand_count;
        $values['dollar_percent'] = ($values['dollar'] * 100) / $projects_capital_dollar_grand;
        $values['hectare_area_percent'] = ($values['hectare_area'] * 100) / $projects_hectare_area_grand;
    }

    ksort($data);
@endphp


<div style="direction: rtl" dir="ltr"
     class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.year')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts_percent')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital')}} $</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital_percent')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.project.hectare_area')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.hectare_percent')}}</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($data as $year => $grand)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{$year}}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $grand['count'] }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ is_numeric($grand['count_percent']) ? number_format($grand['count_percent'], 2) : null }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ is_numeric($grand['dollar']) ? number_format($grand['dollar'], 2) : null }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ is_numeric($grand['dollar_percent']) ? number_format($grand['dollar_percent'], 2) : null }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ is_numeric($grand['hectare_area']) ? number_format($grand['hectare_area'], 2) : null }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ is_numeric($grand['hectare_area_percent']) ? number_format($grand['hectare_area_percent'], 2) : null }}
                    </td>
                </tr>

                @php

                    $totals["count"] += $grand["count"];
                    $totals["count_percent"] += $grand["count_percent"];
                    $totals["dollar"] += $grand["dollar"];
                    $totals["dollar_percent"] += $grand["dollar_percent"];
                    $totals["hectare_area"] += $grand["hectare_area"];
                    $totals["hectare_area_percent"] += $grand["hectare_area_percent"];

                @endphp

                @if($loop->last)
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{trans('resources.total')}}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ $totals['count'] }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ $totals['count_percent'] }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ is_numeric($totals['dollar']) ? number_format($totals['dollar'], 2) : null }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ $totals['dollar_percent'] }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ is_numeric($totals['hectare_area']) ? number_format($totals['hectare_area'], 2) : null }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ $totals['hectare_area_percent'] }}
                        </td>
                    </tr>
                @endif


        @endforeach
        </tbody>

    </table>
</div>
