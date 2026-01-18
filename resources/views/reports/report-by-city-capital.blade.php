@php
    $data = [];
    $grand_count = 0;
    $projects_capital_dollar_grand = 0;
    $totals = [
        "count" => 0,
        "count_percent" => 0,
        "dollar" => 0,
        "dollar_percent" => 0,
    ];

    foreach ($records->groupBy('state_id') as $state => $projects) {
        $year_count = $projects->count();
        $project_capital_dollar = 0;

        foreach ($projects as $project) {
            foreach ($project->projectVariants as $variant) {
                $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
            }
        }

        $grand_count += $year_count;
        $projects_capital_dollar_grand += $project_capital_dollar;

        $data[$state] = [
            'count' => $year_count,
            'dollar' => $project_capital_dollar,
        ];
    }

    foreach ($data as $state => &$values) {
        $values['count_percent'] = ($values['count'] * 100) / $grand_count;
        $values['dollar_percent'] = ($values['dollar'] * 100) / $projects_capital_dollar_grand;
    }

    ksort($data);
@endphp
<div style="direction: rtl" dir="ltr"
     class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.state_single')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts_percent')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital')}} $</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital_percent')}}</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($data as $state => $grand)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>
                            @php
                                $state = \App\Models\State::find($state);
                                if (! empty($state)) {
                                    echo $state->name[app()->getLocale()] ?? null;
                                }
                            @endphp
                        </b>
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
                </tr>

                @php
                    $totals["count"] += $grand["count"];
                    $totals["count_percent"] += $grand["count_percent"];
                    $totals["dollar"] += $grand["dollar"];
                    $totals["dollar_percent"] += $grand["dollar_percent"];
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
                    </tr>
                @endif


        @endforeach
        </tbody>

    </table>
</div>
