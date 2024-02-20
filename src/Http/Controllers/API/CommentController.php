<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        $baseClass = class_basename($modelClass);
        if ($request->filled('comment')) {
            resolve(config('activity-log.activity_log_model'))->query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'description' => 'add a comment'.' in '.$baseClass.' history </br>'
                    .$request->input('comment'),
            ]);
        }

        return new ActivityLogCollection($model->activityLogs()->orderByDesc('created_at')->get());
    }
}
