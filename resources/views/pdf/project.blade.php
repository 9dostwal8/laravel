<html lang="en" dir="{{ $direction }}">
<head>
    <title>{{ $project->project_name['ckb'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('resources/css/fonts.css') }}">

    <style>
        body {
            font-family: @if($direction === 'rtl') "Speda" @endif;
        }
    </style>
</head>
<body>

<div class="px-2 py-8 max-w-xl mx-auto">
    <div class="mb-8 text-gray-700 font-semibold text-lg">{{ $project->project_name['ckb'] }}</div>

{{--    <div class="w-full">--}}
{{--        <div class="flex justify-between border-b pb-3">--}}
{{--            <div>--}}
{{--                <p class="font-bold">@lang('resources.project.status')</p>--}}
{{--                <p class="mt-3">{{ $project->company_name }}</p>--}}
{{--            </div>--}}
{{--            <div>--}}
{{--                <p class="font-bold">@lang('resources.project.file_number')</p>--}}
{{--                <p class="mt-3">{{ $project->file_number }}</p>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <table class="w-full ltr:text-left rtl:text-right mb-8">
        <thead>
        <tr>
            <th class="text-gray-700 font-bold uppercase py-2"></th>
            <th class="text-gray-700 font-bold uppercase py-2"></th>
            <th class="text-gray-700 font-bold uppercase py-2"></th>
            <th class="text-gray-700 font-bold uppercase py-2"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.status')</td>
            <td class="py-4 text-gray-700 text-center">{{ $project->company_name }}</td>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.file_number')</td>
            <td class="py-4 text-gray-700 text-center">{{ $project->company_name }}</td>
        </tr>
        <tr>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.old_file_number')</td>
            <td class="py-4 text-gray-700 text-center">{{ $project->company_name }}</td>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.license_number')</td>
            <td class="py-4 text-gray-700 text-center">{{ $project->company_name }}</td>
        </tr>
        <tr>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.company_name')</td>
            <td class="py-4 text-gray-700 text-center">{{ $project->company_name }}</td>
            <td class="py-4 text-gray-700 font-bold">@lang('resources.project.project_name')</td>
            <td class="py-4 text-gray-700">
                @foreach($project->project_name as $projectName)
                    <p class="text-center">{{ $projectName }}</p>
                @endforeach
            </td>
        </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.village')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.requested_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.execution_time_years')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.execution_time_months')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.hectare_area')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.meter_area')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.project_location')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.place_of_land_allocation')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.land_number')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.type_of_land_allocation')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.land_granting_organization')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.licence_received_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.land_delivered_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.started_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.estimated_project_end_date')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.actual_project_end_date')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.investment_type')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.licensing_authority')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.created_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.updated_at')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>

            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.finance.currency_rate')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.finance.capital_dinar')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.finance.capital_dollar')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.finance.loan_fund')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.finance.non_loan_fund')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>

            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.kurdistan')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.kurdistan_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.foreign')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.foreign_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.iraq')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.iraq_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.seperated_areas')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.fixed_workforce_number.seperated_areas_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>


            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.kurdistan')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.kurdistan_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.foreign')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.foreign_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.iraq')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.iraq_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.seperated_areas')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>
            <tr>
                <td class="py-4 text-gray-700 font-bold">@lang('resources.project.temporary_workforce_number.seperated_areas_full')</td>
                <td class="py-4 text-gray-700">{{ $project->company_name }}</td>
            </tr>

        </tbody>
    </table>
</div>

</body>
</html>
