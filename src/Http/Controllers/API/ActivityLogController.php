<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\DateRangeFilter;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\TermFilter;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __invoke(ExistingRequest $request): ActivityLogCollection
    {
        $query = QueryBuilder::for(config('activity-log.activity_log_model'))
            ->with([
                config('activity-log.user_relationship'),
                config('activity-log.communication_log_relationship'),
            ])
            ->allowedFilters([
                'created_by',
                AllowedFilter::exact('type'),
                AllowedFilter::custom('date', new DateRangeFilter('created_at')),
                AllowedFilter::custom('term', new TermFilter()),
            ])
            ->allowedSorts([
                'id',
                'created_by',
                'activitiable_type',
                'content',
                'created_at',
            ])
            ->defaultSort('-id');

        $query->when($request->has('modelClass'), fn () => $query->where('activitiable_type', $request->input('modelClass')));
        $query->when($request->has('modelId'), fn () => $query->where('activitiable_id', $request->input('modelId')));

        return new ActivityLogCollection(
            $query->paginate(config('activity-log.default_filter_pagination'))
        );
    }
}
