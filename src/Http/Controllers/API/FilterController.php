<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    protected $filterBuilderPath;

    protected $activityLogModel;

    protected $userModel;

    protected $userSearch;

    protected $userSearchRelationship;

    protected $userSearchTerm;

    protected $defaultFilterPagination;

    protected $filterMentionUserRole;

    public function __construct()
    {
        $this->filterBuilderPath = config('activity-log.filter_builder_path', 'App\Support\Filters\FilterBuilder');
        $this->activityLogModel = config('activity-log.activity_log_model', ActivityLog::class);
        $this->userModel = config('activity-log.user_model');
        $this->userSearch = config('activity-log.user_search', 'email');
        $this->userSearchRelationship = config('activity-log.user_search_relationship', []);
        $this->userSearchTerm = config('activity-log.user_search_term', ['email']);
        $this->userSearchTerm = is_array($this->userSearchTerm) ? $this->userSearchTerm : [$this->userSearchTerm];
        $this->defaultFilterPagination = config('default_filter_pagination', 50);
        $this->filterMentionUserRole = config('filter_mention_user_role');
    }

    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse(
            resolve($this->filterBuilderPath)
                ->make()
                ->refineItems('type', 'Type', collect(resolve($this->activityLogModel)->getAvailableTypes()), valueField: 'name', apiMode: false)
                ->dateRange('date', 'Date')
                ->refineItems(
                    'created_by',
                    'User',
                    $this->filterQuery($this->userModel::query()->with($this->userSearchRelationship), $request, searchField: 'filter.created_by', searchTermField: $this->userSearchTerm),
                    searchField: $this->userSearch,
                    itemSelected: $request->filled('filter.created_by')
                )
        );
    }

    public function search(Request $request, string $facet)
    {
        return match ($facet) {
            'created_by' => response()->json(resolve($this->filterBuilderPath)->make()
                ->buildRefineItems($this->filterQuery($this->userModel::query()->with($this->userSearchRelationship), $request, searchTermField: $this->userSearchTerm), searchField: $this->userSearch)
                ->toArray()),
            default => response()->json(),
        };
    }

    private function filterQuery(Builder $query, ?Request $request = null, ?string $searchField = null, array $searchTermField = ['name']): Collection
    {
        if ($request && $request->input('s') !== 'null' && $term = $request->input('s')) {
            $query->where(function (Builder $q) use ($searchTermField, $term) {
                foreach ($searchTermField as $field) {
                    if (is_array($field)) {
                        $query = 'concat(';
                        foreach ($field as $item) {
                            $query .= collect($field)->first() !== $item ? $item : $item . ", ' ', ";
                        }
                        $query .= ")";
                        $q->orWhere(DB::raw($query), 'LIKE', "%$term%");
                        continue;
                    }
                    $parts = explode('.', $field);

                    if (count($parts) > 1) {
                        [$relation, $relationField] = $parts;
                        $q->orWhereHas($relation, fn(Builder $builder) => $builder->where($relationField, 'LIKE', "%$term%"));

                        continue;
                    }


                    $q->orWhere($field, 'LIKE', "%$term%");
                }
            });
        }
        if ($request->filled('filter.admin') && $this->filterMentionUserRole) {
//            // @phpstan-ignore-next-line
            $query->role($this->filterMentionUserRole);
        }

        if ($searchField && $request->filled($searchField)) {
            $idsFilters = explode(',', $request->input($searchField));
            $query->whereIn('id', $idsFilters);
        }

        if ($request->filled('all')) {
            return $query->orderBy($searchTermField[0])->get();
        }

        return $query->orderBy($searchTermField[0])->limit($this->defaultFilterPagination)->get();
    }
}
