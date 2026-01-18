@php
    // ========================================
    // Get date range parameters
    // ========================================
    $fromYear = request()->input('from') ? \Illuminate\Support\Carbon::make(request()->input('from'))->year : null;
    $toYear = request()->input('to') ? \Illuminate\Support\Carbon::make(request()->input('to'))->year : null;
    
    // ========================================
    // Group records by year and activity area type
    // ========================================
    $groupedByYear = [];
    $processedProjects = []; // Track processed projects to prevent duplicates
    
    foreach ($records as $record) {
        if ($record->projectVariants && $record->projectVariants->count() > 0) {
            // Determine year based on project dates (same logic as ReportsQuery)
            $projectYear = null;
            $status = request('status') ? (int) request('status') : null;
            
            if ($status == 1 || $status == 8) {
                // If status is 1 or 8, calculate from requested_at
                if ($record->requested_at) {
                    $projectYear = \Illuminate\Support\Carbon::make($record->requested_at)->year;
                }
            } elseif ($status == 7) {
                // If status is 7, calculate from licence_received_at
                if ($record->licence_received_at) {
                    $projectYear = \Illuminate\Support\Carbon::make($record->licence_received_at)->year;
                }
            } elseif ($status == 5) {
                // If status is 5, calculate from cancellation_date
                if ($record->cancellation_date) {
                    $projectYear = \Illuminate\Support\Carbon::make($record->cancellation_date)->year;
                }
            } else {
                // If no status filter exists, read from requested_at and licence_received_at
                $projectYear = \Illuminate\Support\Carbon::make($record->requested_at)->year ?? \Illuminate\Support\Carbon::make($record->licence_received_at)->year ?? null;
            }
        
            
            // Apply year filtering if date range is specified
            if ($fromYear && $projectYear < $fromYear) {
                continue; // Skip projects before fromYear
            }
            if ($toYear && $projectYear > $toYear) {
                continue; // Skip projects after toYear
            }
            
            // Skip if project already processed (prevent global duplicates)
            if (in_array($record->id, $processedProjects)) {
                continue;
            }
            
            if (!isset($groupedByYear[$projectYear])) {
                $groupedByYear[$projectYear] = [
                    'single' => [], // Type 1: Single activity areas
                    'joint' => []   // Type 2: Joint activity areas
                ];
            }
            
            // Get all activity areas for this project
            $projectActivityAreas = [];
            $hasType2 = false;
            
            foreach ($record->projectVariants as $variant) {
                if ($variant->activityArea) {
                    $projectActivityAreas[] = $variant->activityArea;
                    if ($variant->type == 2) {
                        $hasType2 = true;
                    }
                }
            }
            
            if (count($projectActivityAreas) > 0) {
                if ($hasType2) {
                    // Joint activity areas (Type 2)
                    // Create a unique key for this combination
                    usort($projectActivityAreas, function($a, $b) {
                        return $a->id <=> $b->id;
                    });
                    $combinationKey = implode(',', array_map(function($aa) { return $aa->id; }, $projectActivityAreas));
                    $combinationName = implode(' + ', array_map(function($aa) { return $aa->name[app()->getLocale()] ?? 'Unknown'; }, $projectActivityAreas));
                    
                    if (!isset($groupedByYear[$projectYear]['joint'][$combinationKey])) {
                        $groupedByYear[$projectYear]['joint'][$combinationKey] = [
                            'name' => $combinationName,
                            'projects' => []
                        ];
                    }
                    $groupedByYear[$projectYear]['joint'][$combinationKey]['projects'][] = $record;
                    $processedProjects[] = $record->id; // Mark as processed
                } else {
                    // Single activity area (Type 1 or null/default)
                    // For single projects, add to ONLY THE FIRST activity area to prevent duplicates
                    $firstActivityArea = $projectActivityAreas[0];
                    $activityAreaName = $firstActivityArea->name[app()->getLocale()] ?? 'Unknown';
                    
                    if (!isset($groupedByYear[$projectYear]['single'][$activityAreaName])) {
                        $groupedByYear[$projectYear]['single'][$activityAreaName] = [];
                    }
                    
                    $groupedByYear[$projectYear]['single'][$activityAreaName][] = $record;
                    $processedProjects[] = $record->id; // Mark as processed
                }
            }
        }
    }
    
    // ========================================
    // Calculate statistics for each year and activity area type
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

    foreach ($groupedByYear as $year => $types) {
        // Process single activity areas first (Type 1)
        foreach ($types['single'] as $activityAreaName => $projects) {
            $year_count = count($projects);
            $project_capital_dollar = 0;
            $projects_hectare_area = 0;

            foreach ($projects as $project) {
                // For single activity area projects, sum capital only from variants matching this activity area
                $projectCapitalForThisArea = 0;
                foreach ($project->projectVariants as $variant) {
                    if ($variant->activityArea && 
                        $variant->activityArea->name[app()->getLocale()] == $activityAreaName && 
                        ($variant->type == 1 || is_null($variant->type))) {
                        $projectCapitalForThisArea += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                    }
                }
                $project_capital_dollar += $projectCapitalForThisArea;
                $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
            }

            $grand_count += $year_count;
            $projects_capital_dollar_grand += $project_capital_dollar;
            $projects_hectare_area_grand += $projects_hectare_area;

            $data[$year . '_single_' . $activityAreaName] = [
                'year' => $year,
                'activity_area' => $activityAreaName ,
                'type' => 'single',
                'count' => $year_count,
                'dollar' => $project_capital_dollar,
                'hectare_area' => $projects_hectare_area
            ];
        }
        
        // Process joint activity areas (Type 2)
        foreach ($types['joint'] as $combinationKey => $combination) {
            $projects = $combination['projects'];
            $combinationName = $combination['name'];
            
            $year_count = count($projects);
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

            $data[$year . '_joint_' . $combinationKey] = [
                'year' => $year,
                'activity_area' => $combinationName ,
                'type' => 'joint',
                'count' => $year_count,
                'dollar' => $project_capital_dollar,
                'hectare_area' => $projects_hectare_area
            ];
        }
    }

    // ========================================
    // Calculate percentages for all rows
    // ========================================
    foreach ($data as $key => $values) {
        $data[$key]['count_percent'] = $grand_count > 0 ? ($values['count'] * 100) / $grand_count : 0;
        $data[$key]['dollar_percent'] = $projects_capital_dollar_grand > 0 ? ($values['dollar'] * 100) / $projects_capital_dollar_grand : 0;
        $data[$key]['hectare_area_percent'] = $projects_hectare_area_grand > 0 ? ($values['hectare_area'] * 100) / $projects_hectare_area_grand : 0;
    }

        // ======================================== 
    // Group data by year for better display
    // ========================================
    $groupedData = [];
    
    foreach ($data as $key => $item) {
        $year = $item['year'];
        if (!isset($groupedData[$year])) {
            $groupedData[$year] = [
                'single' => [],
                'joint' => []
            ];
        }
        
        if ($item['type'] == 'single') {
            $groupedData[$year]['single'][] = $item;
        } else {
            $groupedData[$year]['joint'][] = $item;
        }
    }
    
    // Sort years in descending order
    krsort($groupedData);
@endphp

<!-- ======================================== -->
<!-- HTML Structure: Year-based Grouped Report Table -->
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
                    {{trans('resources.year')}}
                </th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    {{trans('resources.activity-area.single')}}
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
        <!-- Table Body: Year-based Grouped Data Rows -->
        <!-- ======================================== -->
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            
            @foreach($groupedData as $year => $types)
                <!-- ======================================== -->
                <!-- Year Group Header -->
                <!-- ======================================== -->
                <tr class="bg-blue-100 dark:bg-blue-900/20">
                    <td colspan="8" class="fi-ta-cell p-3 text-center font-bold text-blue-800 dark:text-blue-200">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        {{trans('resources.year')}} {{ $year }}
                    </td>
                </tr>
                
                <!-- ======================================== -->
                <!-- Single Activity Areas Section -->
                <!-- ======================================== -->
                @if(count($types['single']) > 0)
               
                    
                    @foreach($types['single'] as $item)
                        <!-- ======================================== -->
                        <!-- Single Activity Area Data Row -->
                        <!-- ======================================== -->
                        <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                            
                            <!-- ======================================== -->
                            <!-- Year Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                <b>{{ $item['year'] }}</b>
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Activity Area Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                <b>{{ $item['activity_area'] }}</b>
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Projects Count Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ $item['count'] }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Projects Count Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['count_percent']) ? number_format($item['count_percent'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Capital (Dollar) Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ is_numeric($item['dollar']) ? number_format($item['dollar'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Capital Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['dollar_percent']) ? number_format($item['dollar_percent'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Area (Hectare) Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ is_numeric($item['hectare_area']) ? number_format($item['hectare_area'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Area Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['hectare_area_percent']) ? number_format($item['hectare_area_percent'], 2) : null }}
                            </td>
                        </tr>

                        <!-- ======================================== -->
                        <!-- Calculate totals for final row -->
                        <!-- ======================================== -->
                        @php
                            $totals["count"] += $item["count"];
                            $totals["count_percent"] += $item["count_percent"];
                            $totals["dollar"] += $item["dollar"];
                            $totals["dollar_percent"] += $item["dollar_percent"];
                            $totals["hectare_area"] += $item["hectare_area"];
                            $totals["hectare_area_percent"] += $item["hectare_area_percent"];
                        @endphp
                    @endforeach
                @endif
                
                <!-- ======================================== -->
                <!-- Joint Activity Areas Section -->
                <!-- ======================================== -->
                @if(count($types['joint']) > 0)
               
                    
                    @foreach($types['joint'] as $item)
                        <!-- ======================================== -->
                        <!-- Joint Activity Area Data Row -->
                        <!-- ======================================== -->
                        <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                            
                            <!-- ======================================== -->
                            <!-- Year Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                <b>{{ $item['year'] }}</b>
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Activity Area Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                <b>{{ $item['activity_area'] }}</b>
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Projects Count Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ $item['count'] }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Projects Count Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['count_percent']) ? number_format($item['count_percent'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Capital (Dollar) Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ is_numeric($item['dollar']) ? number_format($item['dollar'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Capital Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['dollar_percent']) ? number_format($item['dollar_percent'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Area (Hectare) Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                {{ is_numeric($item['hectare_area']) ? number_format($item['hectare_area'], 2) : null }}
                            </td>
                            
                            <!-- ======================================== -->
                            <!-- Area Percentage Column -->
                            <!-- ======================================== -->
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                                % {{ is_numeric($item['hectare_area_percent']) ? number_format($item['hectare_area_percent'], 2) : null }}
                            </td>
                        </tr>

                        <!-- ======================================== -->
                        <!-- Calculate totals for final row -->
                        <!-- ======================================== -->
                        @php
                            $totals["count"] += $item["count"];
                            $totals["count_percent"] += $item["count_percent"];
                            $totals["dollar"] += $item["dollar"];
                            $totals["dollar_percent"] += $item["dollar_percent"];
                            $totals["hectare_area"] += $item["hectare_area"];
                            $totals["hectare_area_percent"] += $item["hectare_area_percent"];
                        @endphp
                    @endforeach
                @endif
                
                <!-- ======================================== -->
                <!-- Year Total Row -->
                <!-- ======================================== -->
                @php
                    $yearTotals = [
                        "count" => 0,
                        "dollar" => 0,
                        "hectare_area" => 0
                    ];
                    
                    foreach($types['single'] as $item) {
                        $yearTotals["count"] += $item["count"];
                        $yearTotals["dollar"] += $item["dollar"];
                        $yearTotals["hectare_area"] += $item["hectare_area"];
                    }
                    
                    foreach($types['joint'] as $item) {
                        $yearTotals["count"] += $item["count"];
                        $yearTotals["dollar"] += $item["dollar"];
                        $yearTotals["hectare_area"] += $item["hectare_area"];
                    }
                @endphp
                
                <tr class="bg-gray-100 dark:bg-gray-800/20">
                    <td colspan="2" class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        <i class="fas fa-calculator mr-2"></i>
                        {{trans('resources.total')}} {{trans('resources.year')}} {{ $year }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        {{ $yearTotals['count'] }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        % {{ $grand_count > 0 ? number_format(($yearTotals['count'] * 100) / $grand_count, 2) : '0.00' }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        {{ number_format($yearTotals['dollar'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        % {{ $projects_capital_dollar_grand > 0 ? number_format(($yearTotals['dollar'] * 100) / $projects_capital_dollar_grand, 2) : '0.00' }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        {{ number_format($yearTotals['hectare_area'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                        % {{ $projects_hectare_area_grand > 0 ? number_format(($yearTotals['hectare_area'] * 100) / $projects_hectare_area_grand, 2) : '0.00' }}
                    </td>
                </tr>
            @endforeach
            
            <!-- ======================================== -->
            <!-- Final Row: Display Grand Total -->
            <!-- ======================================== -->
            <tr class="fi-ta-row bg-yellow-100 dark:bg-yellow-900/20">
                
                <!-- ======================================== -->
                <!-- Year and Activity Area Columns (Merged) -->
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
