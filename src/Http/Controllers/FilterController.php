<?php

namespace Dcodegroup\ActivityLog\Controllers;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FilterController extends Controller
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse(
            resolve(config('activity-log.filter_builder_path'))->make()
                ->refineItems('type', 'Type', collect(ActivityLog::getAvailableTypes()), valueField: 'name', apiMode: false)
                ->dateRange('date', 'Date')
                ->refineItems('created_by', 'User', $this->filterQuery(config('activity-log.user_model')::query(), $request, searchTermField: ['email']), searchField: 'full_name')
        );
    }

    public function search(Request $request, string $facet)
    {
        return match ($facet) {
            'created_by' => response()->json(resolve(config('activity-log.filter_builder_path'))->make()
                ->buildRefineItems($this->filterQuery(config('activity-log.user_model')::query(), $request, searchTermField: ['email']), searchField: 'full_name')
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
            $query->where(function ($q) use ($idsFilters) {
                foreach ($idsFilters as $v) {
                    $q->orWhere('id', $v);
                }
            });
        }
        if ($request->filled('all')) {
            return $query->orderBy($searchTermField[0])->get();
        }

        return $query->orderBy($searchTermField[0])->limit(config('activity-log.default_filter_pagination'))->get();
    }
}
