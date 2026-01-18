@php
    // ========================================
    // Get activity_area_id parameter
    // ========================================
    $activityAreaId = request('activity_area_id');
    
    
    // ========================================
    // Main logic: Show specified activity area or all
    // ========================================
    if ($activityAreaId) {
        // ========================================
        // Mode 1: Only specified activity area - grouped by type
        // ========================================
        
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

        // ========================================
        // Group by activity area type and city for specified activity area
        // ========================================
        $groupedByType = [
            'type1' => collect(), // Single activity areas
            'type2' => collect()  // Joint activity areas
        ];
        
        foreach ($records as $record) {
            if ($record->projectVariants && $record->projectVariants->count() > 0) {
                $stateId = $record->state_id;
                
                // Check if this project has the target activity area
                $hasTargetActivityArea = $record->projectVariants->contains('activity_area_id', $activityAreaId);
                
                if ($hasTargetActivityArea) {
                    // Get the activity area name
                    $targetActivityArea = \App\Models\ActivityArea::find($activityAreaId);
                    $activityAreaName = $targetActivityArea ? ($targetActivityArea->name[app()->getLocale()] ?? 'Unknown') : 'Unknown';
                    
                    // Group by Type 1 (individual activity areas)
                    foreach ($record->projectVariants as $variant) {
                        if ($variant->activity_area_id == $activityAreaId) {
                            $variantType = $variant->type ?? 1; // Default to type 1
                            
                            if ($variantType == 1) {
                                // Type 1: Single activity area
                                if (!$groupedByType['type1']->has($activityAreaName)) {
                                    $groupedByType['type1'][$activityAreaName] = collect();
                                }
                                if (!$groupedByType['type1'][$activityAreaName]->has($stateId)) {
                                    $groupedByType['type1'][$activityAreaName][$stateId] = collect();
                                }
                                
                                // Prevent duplicate counting
                                if (!$groupedByType['type1'][$activityAreaName][$stateId]->contains('id', $record->id)) {
                                    $groupedByType['type1'][$activityAreaName][$stateId]->push($record);
                                }
                            }
                        }
                    }
                    
                    // Group by Type 2 (combinations of activity areas)
                    $type2Variants = $record->projectVariants->where('type', 2)->where('activity_area_id', $activityAreaId);
                    if ($type2Variants->count() > 0) {
                        // For Type 2, get all activity areas for this project to create combination key
                        $allType2ActivityAreas = $record->projectVariants
                            ->where('type', 2)
                            ->pluck('activityArea.name.' . app()->getLocale())
                            ->filter()
                            ->unique()
                            ->sort()
                            ->values();
                        
                        if ($allType2ActivityAreas->count() > 0) {
                            $combinationKey = $allType2ActivityAreas->implode(' + ');
                            
                            if (!$groupedByType['type2']->has($combinationKey)) {
                                $groupedByType['type2'][$combinationKey] = collect();
                            }
                            if (!$groupedByType['type2'][$combinationKey]->has($stateId)) {
                                $groupedByType['type2'][$combinationKey][$stateId] = collect();
                            }
                            
                            // Prevent duplicate counting
                            if (!$groupedByType['type2'][$combinationKey][$stateId]->contains('id', $record->id)) {
                                $groupedByType['type2'][$combinationKey][$stateId]->push($record);
                            }
                        }
                    }
                }
            }
        }

        // ========================================
        // Convert Type 1 grouping to required structure
        // ========================================
        foreach ($groupedByType['type1'] as $activityAreaName => $states) {
            foreach ($states as $stateId => $projects) {
                $year_count = $projects->count();
                $project_capital_dollar = 0;
                $projects_hectare_area = 0;

                foreach ($projects as $project) {
                    foreach ($project->projectVariants as $variant) {
                        if ($variant->activity_area_id == $activityAreaId && ($variant->type == 1 || is_null($variant->type))) {
                            $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                        }
                    }
                    $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
                }

                $grand_count += $year_count;
                $projects_capital_dollar_grand += $project_capital_dollar;
                $projects_hectare_area_grand += $projects_hectare_area;

                $data['type1_' . $activityAreaName . '_' . $stateId] = [
                    'type' => 1,
                    'activity_area' => $activityAreaName,
                    'state_id' => $stateId,
                    'count' => $year_count,
                    'dollar' => $project_capital_dollar,
                    'hectare_area' => $projects_hectare_area
                ];
            }
        }

        // ========================================
        // Convert Type 2 grouping to required structure
        // ========================================
        foreach ($groupedByType['type2'] as $combinationKey => $states) {
            foreach ($states as $stateId => $projects) {
                $year_count = $projects->count();
                $project_capital_dollar = 0;
                $projects_hectare_area = 0;

                foreach ($projects as $project) {
                    foreach ($project->projectVariants as $variant) {
                        if ($variant->activity_area_id == $activityAreaId && $variant->type == 2) {
                            $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                        }
                    }
                    $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
                }

                $grand_count += $year_count;
                $projects_capital_dollar_grand += $project_capital_dollar;
                $projects_hectare_area_grand += $projects_hectare_area;

                $data['type2_' . $combinationKey . '_' . $stateId] = [
                    'type' => 2,
                    'activity_area' => $combinationKey,
                    'state_id' => $stateId,
                    'count' => $year_count,
                    'dollar' => $project_capital_dollar,
                    'hectare_area' => $projects_hectare_area
                ];
            }
        }
        
    } else {
        // ========================================
        // Mode 2: All activity areas with Type 1 and Type 2 separation
        // ========================================
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

        // ========================================
        // Group by activity area type and city
        // ========================================
        $groupedByType = [
            'type1' => collect(), // Single activity areas
            'type2' => collect()  // Joint activity areas
        ];
        
        foreach ($records as $record) {
            if ($record->projectVariants && $record->projectVariants->count() > 0) {
                $stateId = $record->state_id;
                
                // Group by Type 1 (individual activity areas)
                foreach ($record->projectVariants as $variant) {
                    if ($variant->activityArea) {
                        $activityAreaName = $variant->activityArea->name[app()->getLocale()] ?? 'Unknown';
                        $variantType = $variant->type ?? 1; // Default to type 1
                        
                        if ($variantType == 1) {
                            // Type 1: Single activity area
                            if (!$groupedByType['type1']->has($activityAreaName)) {
                                $groupedByType['type1'][$activityAreaName] = collect();
                            }
                            if (!$groupedByType['type1'][$activityAreaName]->has($stateId)) {
                                $groupedByType['type1'][$activityAreaName][$stateId] = collect();
                            }
                            $groupedByType['type1'][$activityAreaName][$stateId]->push($record);
                        }
                    }
                }
                
                // Group by Type 2 (combinations of activity areas)
                $type2Variants = $record->projectVariants->where('type', 2);
                if ($type2Variants->count() > 0) {
                    $type2ActivityAreas = $type2Variants
                        ->pluck('activityArea.name.' . app()->getLocale())
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                    
                    if ($type2ActivityAreas->count() > 0) {
                        $combinationKey = $type2ActivityAreas->implode(' + ');
                        
                        if (!$groupedByType['type2']->has($combinationKey)) {
                            $groupedByType['type2'][$combinationKey] = collect();
                        }
                        if (!$groupedByType['type2'][$combinationKey]->has($stateId)) {
                            $groupedByType['type2'][$combinationKey][$stateId] = collect();
                        }
                        $groupedByType['type2'][$combinationKey][$stateId]->push($record);
                    }
                }
            }
        }

        // ========================================
        // Convert Type 1 grouping to required structure
        // ========================================
        foreach ($groupedByType['type1'] as $activityAreaName => $states) {
            foreach ($states as $stateId => $projects) {
                $year_count = $projects->count();
                $project_capital_dollar = 0;
                $projects_hectare_area = 0;

                foreach ($projects as $project) {
                    foreach ($project->projectVariants as $variant) {
                        if ($variant->activityArea && $variant->activityArea->name[app()->getLocale()] == $activityAreaName && $variant->type == 1) {
                            $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                        }
                    }
                    $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
                }

                $grand_count += $year_count;
                $projects_capital_dollar_grand += $project_capital_dollar;
                $projects_hectare_area_grand += $projects_hectare_area;

                $data['type1_' . $activityAreaName . '_' . $stateId] = [
                    'type' => 1,
                    'activity_area' => $activityAreaName,
                    'state_id' => $stateId,
                    'count' => $year_count,
                    'dollar' => $project_capital_dollar,
                    'hectare_area' => $projects_hectare_area
                ];
            }
        }

        // ========================================
        // Convert Type 2 grouping to required structure
        // ========================================
        foreach ($groupedByType['type2'] as $combinationKey => $states) {
            foreach ($states as $stateId => $projects) {
                $year_count = $projects->count();
                $project_capital_dollar = 0;
                $projects_hectare_area = 0;

                foreach ($projects as $project) {
                    foreach ($project->projectVariants as $variant) {
                        if ($variant->type == 2) {
                            $project_capital_dollar += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                        }
                    }
                    $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
                }

                $grand_count += $year_count;
                $projects_capital_dollar_grand += $project_capital_dollar;
                $projects_hectare_area_grand += $projects_hectare_area;

                $data['type2_' . $combinationKey . '_' . $stateId] = [
                    'type' => 2,
                    'activity_area' => $combinationKey,
                    'state_id' => $stateId,
                    'count' => $year_count,
                    'dollar' => $project_capital_dollar,
                    'hectare_area' => $projects_hectare_area
                ];
            }
        }
    }

    // ========================================
    // Calculate percentages for all rows
    // ========================================
    foreach ($data as $city => &$values) {
        $values['count_percent'] = $grand_count > 0 ? ($values['count'] * 100) / $grand_count : 0;
        $values['dollar_percent'] = $projects_capital_dollar_grand > 0 ? ($values['dollar'] * 100) / $projects_capital_dollar_grand : 0;
        $values['hectare_area_percent'] = $projects_hectare_area_grand > 0 ? ($values['hectare_area'] * 100) / $projects_hectare_area_grand : 0;
    }

    // ========================================
    // Group data for better display
    // ========================================
    $groupedData = collect();
    
    if ($activityAreaId) {
        // Mode 1: Group by type and activity area for specified activity area
        foreach ($data as $key => $item) {
            if (isset($item['type'])) {
                if ($item['type'] == 1) {
                    // Type 1: Single activity areas
                    $activityArea = $item['activity_area'];
                    if (!$groupedData->has('type1')) {
                        $groupedData['type1'] = collect();
                    }
                    if (!$groupedData['type1']->has($activityArea)) {
                        $groupedData['type1'][$activityArea] = collect();
                    }
                    $groupedData['type1'][$activityArea]->push($item);
                } else {
                    // Type 2: Joint activity areas
                    $combinationKey = $item['activity_area'];
                    if (!$groupedData->has('type2')) {
                        $groupedData['type2'] = collect();
                    }
                    if (!$groupedData['type2']->has($combinationKey)) {
                        $groupedData['type2'][$combinationKey] = collect();
                    }
                    $groupedData['type2'][$combinationKey]->push($item);
                }
            }
        }
    } else {
        // Mode 2: Group by type and activity area
        foreach ($data as $key => $item) {
            if (isset($item['type'])) {
                if ($item['type'] == 1) {
                    // Type 1: Single activity areas
                    $activityArea = $item['activity_area'];
                    if (!$groupedData->has('type1')) {
                        $groupedData['type1'] = collect();
                    }
                    if (!$groupedData['type1']->has($activityArea)) {
                        $groupedData['type1'][$activityArea] = collect();
                    }
                    $groupedData['type1'][$activityArea]->push($item);
                } else {
                    // Type 2: Joint activity areas
                    $combinationKey = $item['activity_area'];
                    if (!$groupedData->has('type2')) {
                        $groupedData['type2'] = collect();
                    }
                    if (!$groupedData['type2']->has($combinationKey)) {
                        $groupedData['type2'][$combinationKey] = collect();
                    }
                    $groupedData['type2'][$combinationKey]->push($item);
                }
            }
        }
    }

@endphp


<!-- ======================================== -->
<!-- HTML Structure: Grouped Report Table -->
<!-- ======================================== -->
<div style="direction: rtl" dir="ltr"
     class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    
    <!-- ======================================== -->
    <!-- Main Table -->
    <!-- ======================================== -->
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        
        <!-- ======================================== -->
        <!-- Table Header -->
        <!-- ======================================== -->
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.activity-area.single')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.state.single')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.projects_counts')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.projects_counts_percent')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.capital')}} $
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.capital_percent')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.project.hectare_area')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.hectare_percent')}}
                </th>
            </tr>
        </thead>

        <!-- ======================================== -->
        <!-- Table Body: Grouped Data Rows -->
        <!-- ======================================== -->
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            
            @foreach($groupedData as $type => $items)
                @if($type == 'type1')
                    @foreach($items as $activityAreaName => $cities)
                        <!-- ======================================== -->
                        <!-- Activity Area Group Header -->
                        <!-- ======================================== -->
                        <tr class="bg-blue-100 dark:bg-blue-900/20">
                            <td colspan="8" class="fi-ta-cell p-3 text-center font-bold text-blue-800 dark:text-blue-200">
                                <i class="fas fa-layer-group mr-2"></i>
                                {{ $activityAreaName }}
                            </td>
                        </tr>
                        
                        @foreach($cities as $grand)
                            <!-- ======================================== -->
                            <!-- City Data Row -->
                            <!-- ======================================== -->
                            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                
                                                                 <!-- ======================================== -->
                                 <!-- Activity Area Column -->
                                 <!-- ======================================== -->
                                 <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                     <b>
                                         {{ $grand['activity_area'] }}
                                     </b>
                                 </td>
                                
                                <!-- ======================================== -->
                                <!-- City/State Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    <b>
                                        @php $state = \App\Models\State::find($grand['state_id']); @endphp
                                        @if(! empty($state))
                                            {{$state->name[app()->getLocale()] ?? null}}
                                        @endif
                                    </b>
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Projects Count Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ $grand['count'] }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Projects Count Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['count_percent']) ? number_format($grand['count_percent'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Capital (Dollar) Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ is_numeric($grand['dollar']) ? number_format($grand['dollar'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Capital Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['dollar_percent']) ? number_format($grand['dollar_percent'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Area (Hectare) Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ is_numeric($grand['hectare_area']) ? number_format($grand['hectare_area'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Area Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['hectare_area_percent']) ? number_format($grand['hectare_area_percent'], 2) : null }}
                                </td>
                            </tr>

                            <!-- ======================================== -->
                            <!-- Calculate totals for final row -->
                            <!-- ======================================== -->
                            @php
                                $totals["count"] += $grand["count"];
                                $totals["count_percent"] += $grand["count_percent"];
                                $totals["dollar"] += $grand["dollar"];
                                $totals["dollar_percent"] += $grand["dollar_percent"];
                                $totals["hectare_area"] += $grand["hectare_area"];
                                $totals["hectare_area_percent"] += $grand["hectare_area_percent"];
                            @endphp
                        @endforeach
                        
                        <!-- ======================================== -->
                        <!-- Group Total Row for Type 1 -->
                        <!-- ======================================== -->
                        @php
                            $groupTotals = [
                                "count" => 0,
                                "dollar" => 0,
                                "hectare_area" => 0
                            ];
                            
                            foreach($cities as $city) {
                                $groupTotals["count"] += $city["count"];
                                $groupTotals["dollar"] += $city["dollar"];
                                $groupTotals["hectare_area"] += $city["hectare_area"];
                            }
                        @endphp
                        
                        <tr class="bg-gray-100 dark:bg-gray-800/20">
                            <td colspan="2" class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                <i class="fas fa-calculator mr-2"></i>
                                {{trans('resources.total')}} {{ $activityAreaName }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ $groupTotals['count'] }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['count'] * 100) / $grand_count, 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($groupTotals['dollar'], 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['dollar'] * 100) / $projects_capital_dollar_grand, 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($groupTotals['hectare_area'], 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['hectare_area'] * 100) / $projects_hectare_area_grand, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @elseif($type == 'type2')
                    @foreach($items as $combinationKey => $cities)
                        <!-- ======================================== -->
                        <!-- Activity Area Group Header -->
                        <!-- ======================================== -->
                        <tr class="bg-purple-100 dark:bg-purple-900/20">
                            <td colspan="8" class="fi-ta-cell p-3 text-center font-bold text-purple-800 dark:text-purple-200">
                                <i class="fas fa-layer-group mr-2"></i>
                                {{ $combinationKey }}
                            </td>
                        </tr>
                        
                        @foreach($cities as $grand)
                            <!-- ======================================== -->
                            <!-- City Data Row -->
                            <!-- ======================================== -->
                            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                
                                <!-- ======================================== -->
                                <!-- Activity Area Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    <b>
                                        {{ $grand['activity_area'] }}
                                    </b>
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- City/State Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    <b>
                                        @php $state = \App\Models\State::find($grand['state_id']); @endphp
                                        @if(! empty($state))
                                            {{$state->name[app()->getLocale()] ?? null}}
                                        @endif
                                    </b>
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Projects Count Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ $grand['count'] }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Projects Count Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['count_percent']) ? number_format($grand['count_percent'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Capital (Dollar) Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ is_numeric($grand['dollar']) ? number_format($grand['dollar'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Capital Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['dollar_percent']) ? number_format($grand['dollar_percent'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Area (Hectare) Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    {{ is_numeric($grand['hectare_area']) ? number_format($grand['hectare_area'], 2) : null }}
                                </td>
                                
                                <!-- ======================================== -->
                                <!-- Area Percentage Column -->
                                <!-- ======================================== -->
                                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                    % {{ is_numeric($grand['hectare_area_percent']) ? number_format($grand['hectare_area_percent'], 2) : null }}
                                </td>
                            </tr>

                            <!-- ======================================== -->
                            <!-- Calculate totals for final row -->
                            <!-- ======================================== -->
                            @php
                                $totals["count"] += $grand["count"];
                                $totals["count_percent"] += $grand["count_percent"];
                                $totals["dollar"] += $grand["dollar"];
                                $totals["dollar_percent"] += $grand["dollar_percent"];
                                $totals["hectare_area"] += $grand["hectare_area"];
                                $totals["hectare_area_percent"] += $grand["hectare_area_percent"];
                            @endphp
                        @endforeach
                        
                        <!-- ======================================== -->
                        <!-- Group Total Row for Type 2 -->
                        <!-- ======================================== -->
                        @php
                            $groupTotals = [
                                "count" => 0,
                                "dollar" => 0,
                                "hectare_area" => 0
                            ];
                            
                            foreach($cities as $city) {
                                $groupTotals["count"] += $city["count"];
                                $groupTotals["dollar"] += $city["dollar"];
                                $groupTotals["hectare_area"] += $city["hectare_area"];
                            }
                        @endphp
                        
                        <tr class="bg-gray-100 dark:bg-gray-800/20">
                            <td colspan="2" class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                <i class="fas fa-calculator mr-2"></i>
                                {{trans('resources.total')}} {{ $combinationKey }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ $groupTotals['count'] }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['count'] * 100) / $grand_count, 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($groupTotals['dollar'], 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['dollar'] * 100) / $projects_capital_dollar_grand, 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($groupTotals['hectare_area'], 2) }}
                            </td>
                            <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                % {{ number_format(($groupTotals['hectare_area'] * 100) / $projects_hectare_area_grand, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            
            <!-- ======================================== -->
            <!-- Final Row: Display Grand Total -->
            <!-- ======================================== -->
            <tr class="fi-ta-row bg-yellow-100 dark:bg-yellow-900/20">
                
                <!-- ======================================== -->
                <!-- Activity Area and City Columns (Merged) -->
                <!-- ======================================== -->
                <td colspan="2" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    <i class="fas fa-chart-pie mr-2"></i>
                    {{trans('resources.total')}}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Projects Count Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    {{ $totals['count'] }}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Projects Count Percentage Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    % {{ $totals['count_percent'] }}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Capital (Dollar) Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    {{ is_numeric($totals['dollar']) ? number_format($totals['dollar'], 2) : null }}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Capital Percentage Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    % {{ $totals['dollar_percent'] }}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Area (Hectare) Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    {{ is_numeric($totals['hectare_area']) ? number_format($totals['hectare_area'], 2) : null }}
                </td>
                
                <!-- ======================================== -->
                <!-- Total Area Percentage Column -->
                <!-- ======================================== -->
                <td style="background-color: #ffca92" class="fi-ta-cell p-3 text-center font-bold text-yellow-800 dark:text-yellow-200">
                    % {{ $totals['hectare_area_percent'] }}
                </td>
            </tr>
        </tbody>

    </table>
</div>
