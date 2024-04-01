<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class FilterController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse(
            resolve(config('activity-log.filter_builder_path'))
                ->make()
                ->refineItems('type', 'Type', collect(resolve(config('activity-log.activity_log_model'))->getAvailableTypes()), valueField: 'name', apiMode: false)
                ->dateRange('date', 'Date')
                ->refineItems(
                    'created_by',
                    'User',
                    $this->filterQuery(config('activity-log.user_model')::query(), $request, searchField: 'filter.created_by', searchTermField: [config('activity-log.user_search_term')]),
                    searchField: 'full_name',
                    itemSelected: $request->filled('filter.created_by')
                )
        );
    }

    public function search(Request $request, string $facet)
    {
        return match ($facet) {
            'created_by' => response()->json(resolve(config('activity-log.filter_builder_path'))->make()
                ->buildRefineItems($this->filterQuery(config('activity-log.user_model')::query(), $request, searchTermField: [config('activity-log.user_search_term')]), searchField: 'full_name')
                ->toArray()),
            default => response()->json(),
        };
    }

    private function filterQuery(Builder $query, ?Request $request = null, ?string $searchField = null, array $searchTermField = ['name']): Collection
    {
        if ($request && $request->input('s') !== 'null' && $term = $request->input('s')) {
            $query->where(function (Builder $q) use ($searchTermField, $term) {
                $q->where(array_shift($searchTermField), 'LIKE', "%$term%");
                foreach ($searchTermField as $field) {
                    $q->orWhere($field, 'LIKE', "%$term%");
                }
            });
        }

        if ($searchField && $request->filled($searchField)) {
            $idsFilters = explode(',', $request->input($searchField));
            $query->whereIn('id', $idsFilters);
        }
        if ($request->filled('all')) {
            return $query->orderBy($searchTermField[0])->get();
        }

        return $query->orderBy($searchTermField[0])->limit(config('activity-log.default_filter_pagination'))->get();
    }
}
