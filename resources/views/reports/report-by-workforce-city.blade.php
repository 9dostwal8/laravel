@php
    $data = [];
    $total_projects = 0;
    $total_local = 0;
    $total_foreign = 0;
    $total_other = 0;
    $total_workforce = 0;

    $grouped_records = $records->groupBy('state_id');

    // First calculate all totals
    foreach ($grouped_records as $state_id => $state_records) {
        $total_projects += $state_records->count();
        
        $local = $state_records->sum(function($project) {
            return ($project->kurdistan_fixed_workforce_count ?? 0) + 
                   ($project->kurdistan_temporary_workforce_count ?? 0);
        });
        $total_local += $local;
        
        $foreign = $state_records->sum(function($project) {
            return ($project->foreign_fixed_workforce_count ?? 0) + 
                   ($project->foreign_temporary_workforce_count ?? 0);
        });
        $total_foreign += $foreign;

        $other = $state_records->sum(function($project) {
            return ($project->iraq_fixed_workforce_count ?? 0) + 
                   ($project->iraq_temporary_workforce_count ?? 0) +
                   ($project->seperated_areas_fixed_workforce_count ?? 0) + 
                   ($project->seperated_areas_temporary_workforce_count ?? 0);
        });
        $total_other += $other;
    }

    $total_workforce = $total_local + $total_foreign + $total_other;

    // Then calculate percentages based on totals
    foreach ($grouped_records as $state_id => $state_records) {
        $project_count = $state_records->count();
        
        $local_workforce = $state_records->sum(function($project) {
            return ($project->kurdistan_fixed_workforce_count ?? 0) + 
                   ($project->kurdistan_temporary_workforce_count ?? 0);
        });
        
        $foreign_workforce = $state_records->sum(function($project) {
            return ($project->foreign_fixed_workforce_count ?? 0) + 
                   ($project->foreign_temporary_workforce_count ?? 0);
        });

        $other_workforce = $state_records->sum(function($project) {
            return ($project->iraq_fixed_workforce_count ?? 0) + 
                   ($project->iraq_temporary_workforce_count ?? 0) +
                   ($project->seperated_areas_fixed_workforce_count ?? 0) + 
                   ($project->seperated_areas_temporary_workforce_count ?? 0);
        });

        $state_total_workforce = $local_workforce + $foreign_workforce + $other_workforce;
        
        $state = $state_records->first()->state;
        
        $data[$state_id] = [
            'name' => App\Traits\LangSwitcher::getTranslation($state->name),
            'project_count' => $project_count,
            'project_percentage' => $total_projects > 0 ? round(($project_count / $total_projects) * 100, 3) : 0,
            'foreign_workforce' => $foreign_workforce,
            'foreign_percentage' => $total_foreign > 0 ? round(($foreign_workforce / $total_foreign) * 100, 3) : 0,
            'local_workforce' => $local_workforce,
            'local_percentage' => $total_local > 0 ? round(($local_workforce / $total_local) * 100, 3) : 0,
            'other_workforce' => $other_workforce,
            'other_percentage' => $total_other > 0 ? round(($other_workforce / $total_other) * 100, 3) : 0,
            'total_workforce' => $state_total_workforce,
            'total_percentage' => $total_workforce > 0 ? round(($state_total_workforce / $total_workforce) * 100, 3) : 0
        ];
    }

    ksort($data);
@endphp

<div style="direction: rtl" dir="ltr" class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
                <th style="background-color: #ffca92" class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ trans('resources.state.name') }}</th>
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
            @foreach($data as $state_id => $values)
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

                @if($loop->last)
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1">
                            {{ trans('resources.total') }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($total_projects) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format(array_sum(array_column($data, 'project_percentage')), 2) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($total_foreign) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format(array_sum(array_column($data, 'foreign_percentage')), 2) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($total_local) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format(array_sum(array_column($data, 'local_percentage')), 2) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($total_other) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format(array_sum(array_column($data, 'other_percentage')), 2) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            {{ number_format($total_workforce) }}
                        </td>
                        <td style="background-color: #ffca92" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1 text-center">
                            % {{ number_format(array_sum(array_column($data, 'total_percentage')), 2) }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div> 