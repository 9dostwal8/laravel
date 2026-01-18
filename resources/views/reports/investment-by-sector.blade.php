@php
    $projects_sum = 0;
    $projects_capital_dollar = 0;
    $projects_capital_dollar_grand = 0;
    $projects_percent = 0;
    $hectare_area = 0;
    $hectare_area_grand = 0;
@endphp
<div dir="ltr" class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.sector')}}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.project_no')}}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital_in_million_by_investment_type')}} $</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.hectare')}}</th>

                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.by_capital')}} %</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">

        @foreach($records as $activityArea)
            @foreach($activityArea->projects as $project)
                @php
                    $projects_capital_dollar_grand += $project->projectVariants()->get()->last()->capital_dollar ?? 1;
                @endphp
            @endforeach
        @endforeach


        @foreach($records as $activityArea)

            @php
                $projects_sum = 0;
                $projects_capital_dollar = 0;
                $projects_percent = 0;
                $hectare_area = 0;
            @endphp

            @foreach($activityArea->projects as $project)
                @php
                    $projects_current_capital_dollar = $project->projectVariants()->get()->last()->capital_dollar;
                    $projects_capital_dollar += $projects_current_capital_dollar;
                    $projects_percent +=  ( ( ($projects_current_capital_dollar ?? 1) * 100 ) / $projects_capital_dollar_grand ) ;
                @endphp
            @endforeach

            @php
                $projects_sum += $activityArea->projects()->count();
                $hectare_area = $activityArea->projects()->sum('hectare_area');
                $hectare_area_grand += $activityArea->projects()->sum('hectare_area');
            @endphp

            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ $activityArea->name[app()->getLocale()] ?? ''  }}
                </td>
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ $activityArea->projects()->count() }}
                </td>
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($projects_capital_dollar ?? 0, 2)}}
                </td>

                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ number_format($hectare_area ?? 0)}}
                </td>

                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    {{ round($projects_percent, 1)  }}%
                </td>
            </tr>

            @if($loop->last)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td style="padding: 10px 0" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>{{trans('resources.general_total_according_all_sectors')}}</b>
                    </td>
                    <td style="padding: 10px 0;background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>{{$projects_sum}}</b>
                    </td>
                    <td style="padding: 10px 0;background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>{{number_format($projects_capital_dollar_grand, 2)}}</b>
                    </td>
                    <td style="padding: 10px 0;background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>{{number_format($hectare_area_grand)}}</b>
                    </td>
                    <td style="padding: 10px 0;background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        <b>100%</b>
                    </td>
                </tr>
            @endif




        @endforeach
        </tbody>

    </table>
</div>
