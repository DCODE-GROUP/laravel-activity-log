<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\DateRangeFilter;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\TermFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __invoke(ExistingRequest $request): ActivityLogCollection
    {
        $communication = config('activity-log.communication_log_relationship');
        $query = QueryBuilder::for(config('activity-log.activity_log_model'))
            ->when($request->has('modelClass'), fn (Builder $q) => $q->where('activitiable_type', $request->input('modelClass')))
            ->when($request->has('modelId'), fn (Builder $q) => $q->where('activitiable_id', $request->input('modelId')))
            ->where(fn (Builder $builder) => $builder
                ->whereNull('communication_log_id')
                ->orWhere(fn (Builder $builder) => $builder
                    ->whereNotNull('communication_log_id')
                    ->whereNot('title', 'like', '% read an %')
                    ->whereNot('title', 'like', '% view a %'))
            )
            ->with([
                config('activity-log.user_relationship'),
                $communication,
                "$communication.reads",
            ])
            ->allowedFilters([
                'created_by',
                AllowedFilter::exact('id'),
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

        return new ActivityLogCollection(
            $query->paginate($request->has('pagination') ? $request->input('pagination') : config('activity-log.default_filter_pagination'))
        );
    }
}
