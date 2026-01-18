<?php

namespace App\Traits;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

trait ReportsQuery
{
    use InteractsWithTable, InteractsWithForms, LangSwitcher;

    protected static function parseUrl()
    {
        $data = [];
        $query = parse_url(url()->previous())['query'] ?? null;

        if (! empty($query)) {
            $explodes = explode('&', $query);
            foreach ($explodes as $explode) {
                $second_explode = explode('=', $explode);
                $data[$second_explode[0]] = urldecode($second_explode[1]);
            }
        }

        request()->request->add($data);
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.reports');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->content(view(static::$tableContent))->paginated(false);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::customReportQuery();
    }
    
    private static function customReportQuery() {
        if (! request()->get('from')) {
            static::parseUrl();
        }

        $query = parent::getEloquentQuery()
            ->select('*',
                DB::raw('YEAR(licence_received_at) as licence_received_year'),
                DB::raw('YEAR(requested_at) as requested_year'),
                DB::raw('YEAR(first_customs_date) as first_customs_year'),
                DB::raw('YEAR(cancellation_date) as cancellation_year'),
            )
            ->when(request()->has(['from', 'to']), function ($q) {
                $range = [request('from'), request('to')];
            
                switch ((int) request('status')) {
                    case 1:
                    case 8:
                        $q->whereBetween('requested_at', $range);
                        break;
                    case 7:
                        $q->whereBetween('licence_received_at', $range);
                        break;
                    case 5:
                        $q->whereBetween('cancellation_date', $range);
                        break;
                    default:
                        $q->where(function ($sub) use ($range) {
                            $sub->whereBetween('requested_at', $range)
                                ->orWhereBetween('licence_received_at', $range);
                        });
                        break;
                }
            })
            ->when(request('country_id'), function ($q) {
                $q->whereHas('countries', function ($q) {
                    $q->where('country_id', request('country_id'));
                });
            })
            ->when(request('organization_id'), fn($q) => $q->where('organization_id', request('organization_id')))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('project_state'), fn($q) => $q->where('project_state', request('project_state')))
            ->when(! empty(get_user_sectors()), function ($query) {
                $query->whereHas('projectVariants', function ($query) {
                    $query->whereIn('activity_area_id', get_user_sectors());
                });
            })
            ->when(request('activity_area_id'), function ($q) {
                $q->whereHas('projectVariants', function ($q) {
                    $q->where('activity_area_id', request('activity_area_id'));
                });
            })
            ->when(request('department_id'), fn($q) => $q->where('department_id', request('department_id')))
            ->when(request('area_id'), fn($q) => $q->where('area_id', request('area_id')))
            ->when(request('village'), function ($q) {
                $village = request('village');
                $q->where('village', 'LIKE', "%{$village}%");
            })
            ->when(request('investment_type'), fn($q) => $q->where('investment_type', request('investment_type')))
            ->when(request('licensing_authority_id'), fn($q) => $q->where('licensing_authority_id', request('licensing_authority_id')))
            ->when(request()->has(['progress_percentage_from', 'progress_percentage_to']), function ($q) {
                $q->whereHas('progresses', function ($q) {
                    $q->whereBetween('progress_percentage', [
                        request('progress_percentage_from'),
                        request('progress_percentage_to')
                    ]);
                });
            })
            ->when(request('investment_name'), function ($q) {
                $q->whereHas('investors', function ($q) {
                    $q->where('name.' . app()->getLocale(), 'LIKE', "%" . request('investment_name') . "%");
                });
            })
            ->when(request('project_name'), function ($q) {
                $q->where('project_name.' . app()->getLocale(), 'LIKE', "%" . request('project_name') . "%");
            })
            ->when(request()->has(['first_customs_date_from', 'first_customs_date_to']), function ($q) {
                $q->whereBetween('first_customs_date', [request('first_customs_date_from'), request('first_customs_date_to')]);
            })
            ->when(request()->has(['cancellation_date_from', 'cancellation_date_to']), function ($q) {
                $q->whereBetween('cancellation_date', [request('cancellation_date_from'), request('cancellation_date_to')]);
            });

        return $query;
    }
}
