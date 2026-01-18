@php

    
    // Step 1: Group JOINT projects (type=2) by COMBINATION of activity areas
    $groupedRecords = collect();
    $allJointProjects = collect(); // Track all joint projects globally
    
    foreach ($records as $record) {
        if ($record->projectVariants && $record->projectVariants->count() > 0) {
            // Only process projects that have joint type variants (type = 2)
            if ($record->projectVariants->contains('type', 2)) {
                // Track this joint project globally
                $allJointProjects->push($record);
                
                // Get variants with type=2 for this joint project
                $jointVariants = $record->projectVariants->filter(function ($variant) {
                    return $variant->type == 2;
                });
                
                if ($jointVariants->count() > 0) {
                    // Get the COMBINATION of activity areas for this project
                    $activityAreaCombination = $jointVariants
                        ->pluck('activityArea.name.' . app()->getLocale())
                        ->filter()
                        ->sort() // Sort to ensure consistent combination keys
                        ->values()
                        ->implode(' + ');
                    
                    if ($activityAreaCombination) {
                        if (!$groupedRecords->has($activityAreaCombination)) {
                            $groupedRecords[$activityAreaCombination] = collect();
                        }
                        
                        // Add project to this combination group
                        $groupedRecords[$activityAreaCombination]->push($record);
                    }
                }
            }
        }
        // Projects with no variants or no joint variants are ignored
    }
    
    // Step 2: Calculate data for each activity area COMBINATION (Joint projects only)
    $data = [];
    
    foreach ($groupedRecords as $combination => $projects) {
        $combinationCount = $projects->count();
        $combinationCapital = 0;
        $combinationHectare = 0;
        
        foreach ($projects as $project) {
            $combinationHectare += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
            
            // Sum capital from ALL joint variants (type=2) for this project
            $jointVariants = $project->projectVariants->filter(function ($variant) {
                return $variant->type == 2; // Only type=2 variants
            });
            
            foreach ($jointVariants as $variant) {
                $combinationCapital += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
            }
        }
        
        $data[$combination] = [
            'count' => $combinationCount,
            'dollar' => $combinationCapital,
            'hectare_area' => $combinationHectare
        ];
    }
    
    // Debug info
    $debugInfo = [
        'total_records' => $records->count(),
        'unique_joint_projects_total' => $allJointProjects->unique('id')->count(),
        'combinations_found' => $groupedRecords->keys()->toArray(),
        'joint_projects_by_combination' => $groupedRecords->map->count(),
        'total_joint_projects' => $records->filter(function($record) {
            return $record->projectVariants && $record->projectVariants->contains('type', 2);
        })->count(),
        'projects_with_no_variants_ignored' => $records->filter(function($record) {
            return $record->projectVariants->count() == 0;
        })->count(),
        'activity_area_filter' => request('activity_area_id') ? 'Applied' : 'Not Applied'
    ];
    
    // Step 4: Calculate percentages
    $grandTotal = array_sum(array_column($data, 'count'));
    $grandCapital = array_sum(array_column($data, 'dollar'));
    $grandHectare = array_sum(array_column($data, 'hectare_area'));
    
    foreach ($data as $area => &$values) {
        $values['count_percent'] = $grandTotal > 0 ? ($values['count'] * 100) / $grandTotal : 0;
        $values['dollar_percent'] = $grandCapital > 0 ? ($values['dollar'] * 100) / $grandCapital : 0;
        $values['hectare_area_percent'] = $grandHectare > 0 ? ($values['hectare_area'] * 100) / $grandHectare : 0;
    }
    
    // Initialize totals for display
    $totals = [
        "count" => 0,
        "count_percent" => 0,
        "dollar" => 0,
        "dollar_percent" => 0,
        "hectare_area" => 0,
        "hectare_area_percent" => 0
    ];
@endphp
<div style="direction: rtl" dir="ltr"
     class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.activity-area.single')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts_percent')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital')}} $</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital_percent')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.project.hectare_area')}}</th>
            <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.hectare_percent')}}</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($data as $activity_area => $grand)
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    <b>
                        {{$activity_area}}
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
                    <td colspan="1" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{trans('resources.total')}}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $totals['count'] }}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ number_format($totals['count_percent'], 2) }}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($totals['dollar'], 2) }}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ number_format($totals['dollar_percent'], 2) }}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ number_format($totals['hectare_area'], 2) }}
                    </td>
                    <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ number_format($totals['hectare_area_percent'], 2) }}
                    </td>
                </tr>
            @endif


        @endforeach
        </tbody>

    </table>
</div>

<!-- Debug Information (remove after testing) -->
@if(app()->environment('local') || request()->has('debug'))
<div style="margin-top: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px; direction: ltr;">
    <h4>Debug Information:</h4>
    <pre>{{ json_encode($debugInfo, JSON_PRETTY_PRINT) }}</pre>
</div>
@endif

