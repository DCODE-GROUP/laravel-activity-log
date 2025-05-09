<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\DateRangeFilter;
use Dcodegroup\ActivityLog\Support\QueryBuilder\Filters\TermFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function __invoke(ExistingRequest $request): ActivityLogCollection
    {
        $communication = config('activity-log.communication_log_relationship');

        // @phpstan-ignore-next-line
        $queryBuilder = QueryBuilder::for(config('activity-log.activity_log_model'))->where(fn($query) => $query
            ->when($request->has('modelClass'), fn(Builder $q) => $q->where('activitiable_type', $request->input('modelClass')))
            ->when($request->has('modelId'), fn(Builder $q) => $q->where('activitiable_id', $request->input('modelId'))));

        if ($request->has('modelClass') && $request->has('modelId') && $request->has('extra_models')) {
            $modelClass = $request->input('modelClass');
            $modelId = $request->input('modelId');
            $extraModels = explode(',', $request->input('extra_models'));
            if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
                $model = $modelClass::find($modelId);
                if ($model) {
                    foreach ($extraModels as $relation) {
                        if (method_exists($model, $relation)) {
                            if ($model->$relation() instanceof Relation) {
                                $relatedItems = $model->$relation;
                                $relatedTable = $model->$relation()->getRelated();

                                if ($relatedItems instanceof Collection) {
                                    $queryBuilder->orWhere(fn ($query) => $query->where('activitiable_type', get_class($relatedTable))->whereIn('activitiable_id', $relatedItems->pluck('id')->toArray()));
                                } elseif ($relatedItems) {
                                    $queryBuilder->orWhere(fn ($query) => $query->where('activitiable_type', get_class($relatedTable))->where('activitiable_id', $relatedItems->pluck('id')->toArray()));
                                }
                            }
                        }
                    }
                }
            }
        }
        $query = $queryBuilder
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
                AllowedFilter::custom('term', new TermFilter),
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
