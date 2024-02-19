<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Dcodegroup\ActivityLog\Support\DateRangeFilter;
use Dcodegroup\ActivityLog\Support\TermFilter;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __invoke(ExistingRequest $request): ActivityLogCollection
    {
        $query = QueryBuilder::for(ActivityLog::class)
            ->with(['user', 'communicationLog'])
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
