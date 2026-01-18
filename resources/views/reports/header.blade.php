<div>
    @if(str_contains(request()->url(), 'report-by-'))
<style>
    
        
    @media print {
        body {
            visibility: hidden;
        }
        .fi-main {
            visibility: visible;
        }
        .fi-topbar{
            display: none;
        }
        .print{
            display: none;
        }
        .fi-pagination{
            display: none;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
            
        }
        .print-btn-main{
            display: none;
        }
       
    }
    
    table thead tr th{
        padding: 5px !important;
    }
    table tbody tr td{
        
    }
    .fi-sidebar {
        display: none;
    }
    .fi-main{
        padding: 0 10px !important;
        margin: 0 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .fi-main .fi-resource-list-records-page .container{
        width: 100% !important;
        max-width: 100% !important;
    }

    .print-btn{
        margin: 20px 0;
        display: inline-block;
        border: solid 1px #2463eb;
        border-radius: 6px;
        padding: 5px 20px;
        font-weight: 600;
        color: #2463eb;
        cursor: pointer;
    }

    .report-logo{
        width: 30%;
        margin: 0 auto;
    }
    .report-list-details{

    }
    .report-list-details li{
        margin-bottom: 5px;
        font-weight: 700;
    }
    .fi-ta-table tr td, .fi-ta-table tr th{
        border: solid 1px #686868;
    }
    .fi-ta-ctn {
        border-radius: 0;
    }
</style>

<div class="container">

    <div class="print-btn-main">
        <a class="print-btn" onclick="window.print()">{{trans('resources.print')}}</a>
        <a class="print-btn" target="_blank" href="/admin/report-form">{{trans('resources.report_form')}}</a>
        <a class="print-btn" target="_blank" href="/admin">{{trans('resources.dashboard')}}</a>

    </div>
                
    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-3 gap-3">

        <div class="text-center">
            <ul class="report-list-details">
                <li>
                    {{trans('resources.board_of_investment')}}
                </li>
                <li>
                    <p>{{trans('resources.office_of_research_and_information')}}</p>
                    <p>{{trans('resources.directorate_of_information')}}</p>
                </li>
                <li>
                    {{trans('resources.project.status')}} : {{request('status') ? \App\Enums\ProjectStatus::tryFrom(request('status'))->getLabel() : trans('resources.all')}}
                </li>
                <li>
                    {{trans('resources.country.single')}} : {{ trans('resources.all') }}
                </li>
                <li>
                    {{trans('resources.project.licensing_authority')}} :
                    @if(request()->has('licensing_authority_id'))
                        @php $licensing_authority = \App\Models\LicensingAuthority::find(request('licensing_authority_id')); @endphp
                        @if(! empty($licensing_authority))
                            {{$licensing_authority->name[app()->getLocale()] ?? ''}}
                        @endif
                    @else
                        {{ trans('resources.all') }}
                    @endif

                </li>
            </ul>
        </div>

        <div class="text-center">
            <img class="report-logo" src="{{asset('images/img.png')}}" alt="">
            <p class="mt-2">
                {{trans('resources.board_of_investment')}}
            </p>
        </div>

        <div class="text-center">
            <ul class="report-list-details">
                <li>
                    {{trans('resources.report')}}: {{ trans('resources.the_utilization_based_on_the.' . request()->get('type')) }}
                </li>
                <li>
                    <span>
                        {{trans('resources.from_date')}}: {{request()->get('from')}}
                    </span>
                    <span>
                        {{trans('resources.to_date')}}: {{request()->get('to')}}
                    </span>
                </li>
                <li>
                    {{trans('resources.project.investment_type')}} : {{request('investment_type') ? \App\Enums\InvestmentTypeEnum::tryFrom(request('investment_type'))->getLabel() : trans('resources.all')}}
                </li>

                <li>
                    {{trans('resources.activity-area.single')}} : {{request('activity_area_id') ? \App\Models\ActivityArea::find(request('activity_area_id'))?->name[app()->getLocale()] : trans('resources.all')}}
                </li>

            </ul>
        </div>

    </div>
</div>

@endif

</div>