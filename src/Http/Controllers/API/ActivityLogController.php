<?php

namespace Dcodegroup\ActivityLog\Controllers\API;

use Dcodegroup\ActivityLog\Support\DateRangeFilter;
use Dcodegroup\ActivityLog\Support\TermFilter;
use Illuminate\Routing\Controller;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Resources\ActivityLog as ActivityLogResource;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(ExistingRequest $request): AnonymousResourceCollection
    {
        /** @phpstan-ignore-next-line */
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

        return ActivityLogResource::collection(
            $query->paginate(config('activity-log.default_filter_pagination'))
        );
    }
}
