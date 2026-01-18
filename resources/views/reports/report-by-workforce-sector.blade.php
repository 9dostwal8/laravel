@php
    $data = [];
    $total_projects = 0;
    $total_local = 0;
    $total_foreign = 0;
    $total_other = 0;
    $total_workforce = 0;
    
    // Separate single and joint projects
    $single_projects = [];
    $joint_projects = [];
    $single_workforce = [
        'local' => 0,
        'foreign' => 0,
        'other' => 0,
        'total' => 0
    ];
    $joint_workforce = [
        'local' => 0,
        'foreign' => 0,
        'other' => 0,
        'total' => 0
    ];

    // Categorize projects as single or joint
    foreach($records as $project) {
        if ($project->projectVariants && $project->projectVariants->count() > 0) {
            // Get unique activity_area_ids for this project
            $unique_activity_areas = $project->projectVariants
                ->pluck('activity_area_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            
            if (!empty($unique_activity_areas)) {
                if (count($unique_activity_areas) == 1) {
                    // Single activity area (Type 1)
                    $single_projects[] = $project;
                    
                    // Calculate workforce for single projects
                    $single_workforce['local'] += ($project->kurdistan_fixed_workforce_count ?? 0) + 
                                                ($project->kurdistan_temporary_workforce_count ?? 0);
                    $single_workforce['foreign'] += ($project->foreign_fixed_workforce_count ?? 0) + 
                                                   ($project->foreign_temporary_workforce_count ?? 0);
                    $single_workforce['other'] += ($project->iraq_fixed_workforce_count ?? 0) + 
                                                 ($project->iraq_temporary_workforce_count ?? 0) +
                                                 ($project->seperated_areas_fixed_workforce_count ?? 0) + 
                                                 ($project->seperated_areas_temporary_workforce_count ?? 0);
                } else {
                    // Joint activity areas (Type 2)
                    $joint_projects[] = $project;
                    
                    // Calculate workforce for joint projects
                    $joint_workforce['local'] += ($project->kurdistan_fixed_workforce_count ?? 0) + 
                                               ($project->kurdistan_temporary_workforce_count ?? 0);
                    $joint_workforce['foreign'] += ($project->foreign_fixed_workforce_count ?? 0) + 
                                                  ($project->foreign_temporary_workforce_count ?? 0);
                    $joint_workforce['other'] += ($project->iraq_fixed_workforce_count ?? 0) + 
                                                ($project->iraq_temporary_workforce_count ?? 0) +
                                                ($project->seperated_areas_fixed_workforce_count ?? 0) + 
                                                ($project->seperated_areas_temporary_workforce_count ?? 0);
                }
            }
        }
    }
    
    // Calculate totals for single projects
    $single_workforce['total'] = $single_workforce['local'] + $single_workforce['foreign'] + $single_workforce['other'];
    $joint_workforce['total'] = $joint_workforce['local'] + $joint_workforce['foreign'] + $joint_workforce['other'];
    
    $total_projects = count($single_projects);
    $total_local = $single_workforce['local'];
    $total_foreign = $single_workforce['foreign'];
    $total_other = $single_workforce['other'];
    $total_workforce = $single_workforce['total'];

    // Group single projects by activity area
    $grouped_single_records = [];
    
    foreach($single_projects as $project) {
        if ($project->projectVariants && $project->projectVariants->count() > 0) {
            $activity_area_id = $project->projectVariants->first()->activity_area_id;
            
            if (!isset($grouped_single_records[$activity_area_id])) {
                $grouped_single_records[$activity_area_id] = [];
            }
            $grouped_single_records[$activity_area_id][] = $project;
        }
    }

    // Calculate statistics for each single activity area
    foreach ($grouped_single_records as $activity_area_id => $sector_records) {
        $project_count = count($sector_records);
        
        $local_workforce = array_sum(array_map(function($project) {
            return ($project->kurdistan_fixed_workforce_count ?? 0) + 
                   ($project->kurdistan_temporary_workforce_count ?? 0);
        }, $sector_records));
        
        $foreign_workforce = array_sum(array_map(function($project) {
            return ($project->foreign_fixed_workforce_count ?? 0) + 
                   ($project->foreign_temporary_workforce_count ?? 0);
        }, $sector_records));

        $other_workforce = array_sum(array_map(function($project) {
            return ($project->iraq_fixed_workforce_count ?? 0) + 
                   ($project->iraq_temporary_workforce_count ?? 0) +
                   ($project->seperated_areas_fixed_workforce_count ?? 0) + 
                   ($project->seperated_areas_temporary_workforce_count ?? 0);
        }, $sector_records));

        $sector_total_workforce = $local_workforce + $foreign_workforce + $other_workforce;
        
        // Get activity area name
        $activity_area_name = 'Unknown';
        $first_project = $sector_records[0];
        if ($first_project && $first_project->projectVariants->count() > 0) {
            $matching_variant = $first_project->projectVariants->firstWhere('activity_area_id', $activity_area_id);
            if ($matching_variant && $matching_variant->activityArea) {
                $activity_area_name = $matching_variant->activityArea->name[app()->getLocale()] ?? $matching_variant->activityArea->name ?? 'Unknown';
            }
        }

        $data[$activity_area_id] = [
            'name' => $activity_area_name,
            'project_count' => $project_count,
            'project_percentage' => $total_projects > 0 ? round(($project_count / $total_projects) * 100, 3) : 0,
            'foreign_workforce' => $foreign_workforce,
            'foreign_percentage' => $total_foreign > 0 ? round(($foreign_workforce / $total_foreign) * 100, 3) : 0,
            'local_workforce' => $local_workforce,
            'local_percentage' => $total_local > 0 ? round(($local_workforce / $total_local) * 100, 3) : 0,
            'other_workforce' => $other_workforce,
            'other_percentage' => $total_other > 0 ? round(($other_workforce / $total_other) * 100, 3) : 0,
            'total_workforce' => $sector_total_workforce,
            'total_percentage' => $total_workforce > 0 ? round(($sector_total_workforce / $total_workforce) * 100, 3) : 0
        ];
    }

    // Add joint projects row (هاوبه‌ش)
    if (count($joint_projects) > 0) {
        $joint_project_count = count($joint_projects);
        
        $data['joint'] = [
            'name' => 'هاوبه‌ش',
            'project_count' => $joint_project_count,
            'project_percentage' => ($total_projects + $joint_project_count) > 0 ? round(($joint_project_count / ($total_projects + $joint_project_count)) * 100, 3) : 0,
            'foreign_workforce' => $joint_workforce['foreign'],
            'foreign_percentage' => ($total_foreign + $joint_workforce['foreign']) > 0 ? round(($joint_workforce['foreign'] / ($total_foreign + $joint_workforce['foreign'])) * 100, 3) : 0,
            'local_workforce' => $joint_workforce['local'],
            'local_percentage' => ($total_local + $joint_workforce['local']) > 0 ? round(($joint_workforce['local'] / ($total_local + $joint_workforce['local'])) * 100, 3) : 0,
            'other_workforce' => $joint_workforce['other'],
            'other_percentage' => ($total_other + $joint_workforce['other']) > 0 ? round(($joint_workforce['other'] / ($total_other + $joint_workforce['other'])) * 100, 3) : 0,
            'total_workforce' => $joint_workforce['total'],
            'total_percentage' => ($total_workforce + $joint_workforce['total']) > 0 ? round(($joint_workforce['total'] / ($total_workforce + $joint_workforce['total'])) * 100, 3) : 0
        ];
    }

    ksort($data);
    
    // Calculate grand totals including joint projects
    $grand_total_projects = $total_projects + count($joint_projects);
    $grand_total_local = $total_local + $joint_workforce['local'];
    $grand_total_foreign = $total_foreign + $joint_workforce['foreign'];
    $grand_total_other = $total_other + $joint_workforce['other'];
    $grand_total_workforce = $total_workforce + $joint_workforce['total'];
@endphp

<div style="direction: rtl" dir="ltr" class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.sector') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project.count') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.project.percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.foreign') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.foreign_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.local') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.local_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.other') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.other_percentage') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.total') }}</th>
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.workforce.total_percentage') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            @foreach($data as $activity_area_id => $values)
                @if($activity_area_id !== 'joint')
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1">
                            {{ $values['name'] }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($values['project_count']) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format($values['project_percentage'], 2) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($values['foreign_workforce']) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format($values['foreign_percentage'], 2) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($values['local_workforce']) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format($values['local_percentage'], 2) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($values['other_workforce']) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format($values['other_percentage'], 2) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($values['total_workforce']) }}
                        </td>
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format($values['total_percentage'], 2) }}
                        </td>
                    </tr>
                @endif
            @endforeach
            
            <!-- هاوبه‌ش (جونت) Row -->
            @if(isset($data['joint']))
                <tr class="fi-ta-row bg-purple-50 dark:bg-purple-900/20 [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-purple-100 dark:hover:bg-purple-900/30">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 font-bold">
                        {{ $data['joint']['name'] }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        {{ number_format($data['joint']['project_count']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        % {{ number_format($data['joint']['project_percentage'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        {{ number_format($data['joint']['foreign_workforce']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        % {{ number_format($data['joint']['foreign_percentage'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        {{ number_format($data['joint']['local_workforce']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        % {{ number_format($data['joint']['local_percentage'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        {{ number_format($data['joint']['other_workforce']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        % {{ number_format($data['joint']['other_percentage'], 2) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        {{ number_format($data['joint']['total_workforce']) }}
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                        % {{ number_format($data['joint']['total_percentage'], 2) }}
                    </td>
                </tr>
            @endif

            <!-- Grand Total Row -->
            <tr class="fi-ta-row bg-yellow-100 dark:bg-yellow-900/20 [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-yellow-200 dark:hover:bg-yellow-900/30">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 font-bold">
                    {{ trans('resources.total') }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    {{ number_format($grand_total_projects) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    % 100.00
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    {{ number_format($grand_total_foreign) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    % 100.00
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    {{ number_format($grand_total_local) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    % 100.00
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    {{ number_format($grand_total_other) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    % 100.00
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    {{ number_format($grand_total_workforce) }}
                </td>
                <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center font-bold">
                    % 100.00
                </td>
            </tr>
        </tbody>
    </table>
</div> 