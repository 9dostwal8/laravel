@php
    $investment_type = request('investment_type');
    
    
    $groupedRecords = collect();
    
    if ($investment_type == 4) {
        // For investment type 4: Group by country combinations
        foreach ($records as $record) {
            // Get unique countries directly from project
            $countries = $record->originalCountries
                ->pluck('name.' . app()->getLocale())
                ->filter()
                ->unique()
                ->sort() // Sort to ensure consistent combination keys
                ->values();

            if($countries->isEmpty()) {
                continue;
            }
            
            // Create a unique key for the country combination
            $combinationKey = $countries->implode(' + ');
            
            if (!$groupedRecords->has($combinationKey)) {
                $groupedRecords[$combinationKey] = collect();
            }
            
            // Only add if not already present (avoid duplicates)
            if (!$groupedRecords[$combinationKey]->contains('id', $record->id)) {
                $groupedRecords[$combinationKey]->push($record);
            }
        }
    } elseif ($investment_type) {
        // For specific investment types
        foreach ($records as $record) {
            if ($investment_type == 1) {
                // For investment type 1 (National): Use originalCountries from project
                $countries = $record->originalCountries
                    ->pluck('name.' . app()->getLocale())
                    ->filter()
                    ->unique();

                if($countries->isEmpty()) {
                    // If no countries specified, use a default label
                    $countryName = trans('resources.iraq');
                } else {
                    // Use the first country name
                    $countryName = $countries->first();
                }
                
                if (!$groupedRecords->has($countryName)) {
                    $groupedRecords[$countryName] = collect();
                }
                
                // Only add if not already present (avoid duplicates)
                if (!$groupedRecords[$countryName]->contains('id', $record->id)) {
                    $groupedRecords[$countryName]->push($record);
                }
            } else {
                // For investment types 2, etc: Group by individual countries from originalCountries
                $countries = $record->originalCountries
                    ->pluck('name.' . app()->getLocale())
                    ->filter()
                    ->unique(); // Remove duplicate country names

                if($countries->isEmpty()) {
                    continue;
                }
                
                // Add record to each country group (only once per country)
                foreach ($countries as $country) {
                    if (!$groupedRecords->has($country)) {
                        $groupedRecords[$country] = collect();
                    }
                    
                    // Only add if not already present (avoid duplicates)
                    if (!$groupedRecords[$country]->contains('id', $record->id)) {
                        $groupedRecords[$country]->push($record);
                    }
                }
            }
        }
    } else {
        // If no investment_type specified: Mixed logic based on project type
        foreach ($records as $record) {
            $projectInvestmentType = $record->investment_type?->value;
            
            if ($projectInvestmentType && $projectInvestmentType == 1) {
                // For national projects: Use originalCountries from project
                $countries = $record->originalCountries
                    ->pluck('name.' . app()->getLocale())
                    ->filter()
                    ->unique();

                if($countries->isEmpty()) {
                    // If no countries specified, use Iraq as default
                    $countryName = trans('resources.iraq_national');
                } else {
                    // Use the first country name
                    $countryName = $countries->first();
                }
                
                if (!$groupedRecords->has($countryName)) {
                    $groupedRecords[$countryName] = collect();
                }
                
                if (!$groupedRecords[$countryName]->contains('id', $record->id)) {
                    $groupedRecords[$countryName]->push($record);
                }
            } elseif ($projectInvestmentType && $projectInvestmentType == 4) {
                // For joint projects: Group by country combinations from originalCountries
                $countries = $record->originalCountries
                    ->pluck('name.' . app()->getLocale())
                    ->filter()
                    ->unique();

                // Create a unique key for the country combination
                // If no countries, use a default label
                $sortedCountries = $countries->sort()->values();
                $combinationKey = $sortedCountries->isEmpty() ? trans('resources.iraq_national') : $sortedCountries->implode(' + ');
                
                if (!$groupedRecords->has($combinationKey)) {
                    $groupedRecords[$combinationKey] = collect();
                }
                
                if (!$groupedRecords[$combinationKey]->contains('id', $record->id)) {
                    $groupedRecords[$combinationKey]->push($record);
                }
            } elseif ($projectInvestmentType && $projectInvestmentType == 2) {
                // For foreign projects: Group by individual countries from originalCountries
                $countries = $record->originalCountries
                    ->pluck('name.' . app()->getLocale())
                    ->filter()
                    ->unique();

                if(!$countries->isEmpty()) {
                    foreach ($countries as $country) {
                        if (!$groupedRecords->has($country)) {
                            $groupedRecords[$country] = collect();
                        }
                        
                        if (!$groupedRecords[$country]->contains('id', $record->id)) {
                            $groupedRecords[$country]->push($record);
                        }
                    }
                }
            }
            // Skip projects with invalid or null investment_type
        }
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

    foreach ($groupedRecords as $country => $projects) {
        $project_count = $projects->count();
        $project_capital_dollar = 0;
        $projects_hectare_area = 0;

        foreach ($projects as $project) {
            // Calculate total capital from all variants for this project (sum once per project)
            $project_total_capital = 0;
            if ($project->projectVariants && $project->projectVariants->count() > 0) {
                foreach ($project->projectVariants as $variant) {
                    $project_total_capital += is_numeric($variant->capital_dollar) ? $variant->capital_dollar : 0;
                }
            }
            $project_capital_dollar += $project_total_capital;
            
            // Add hectare area (once per project)
            $projects_hectare_area += is_numeric($project->hectare_area) ? $project->hectare_area : 0;
        }

        $grand_count += $project_count;
        $projects_capital_dollar_grand += $project_capital_dollar;
        $projects_hectare_area_grand += $projects_hectare_area;

        $data[$country] = [
            'count' => $project_count,
            'dollar' => $project_capital_dollar,
            'hectare_area' => $projects_hectare_area
        ];
    }

    foreach ($data as $country => &$values) {
        $values['count_percent'] = $grand_count > 0 ? ($values['count'] * 100) / $grand_count : 0;
        $values['dollar_percent'] = $projects_capital_dollar_grand > 0 ? ($values['dollar'] * 100) / $projects_capital_dollar_grand : 0;
        $values['hectare_area_percent'] = $projects_hectare_area_grand > 0 ? ($values['hectare_area'] * 100) / $projects_hectare_area_grand : 0;
    }

@endphp
<div style="direction: rtl" dir="ltr"
     class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.investment_type')}}</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                @if($investment_type == 4)
                    {{trans('resources.country.combination')}}
                @else
                    {{trans('resources.country.single')}}
                @endif
            </th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts')}}</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.projects_counts_percent')}}</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital')}} $</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.capital_percent')}}</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.project.hectare_area')}}</th>
            <th style="background-color: #ffca92"
                class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{trans('resources.hectare_percent')}}</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($data as $country => $grand)
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    <b>
                        @if($investment_type)
                            {{\App\Enums\InvestmentTypeEnum::from($investment_type)->getLabel()}}
                        @else
                            {{trans('resources.project.all_investment_types')}}
                        @endif
                    </b>
                </td>
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                    <b>
                        {{ $country }}
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
                    <td colspan="2"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{trans('resources.total')}}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ $totals['count'] }}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ $totals['count_percent'] }}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ is_numeric($totals['dollar']) ? number_format($totals['dollar'], 2) : null }}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ $totals['dollar_percent'] }}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        {{ is_numeric($totals['hectare_area']) ? number_format($totals['hectare_area'], 2) : null }}
                    </td>
                    <td style="background-color: #ffca92"
                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                        % {{ $totals['hectare_area_percent'] }}
                    </td>
                </tr>
            @endif

        @endforeach
        </tbody>

    </table>
</div>
