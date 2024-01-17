<?php

namespace Dcodegroup\ActivityLog\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Dcodegroup\ActivityLog\Http\Requests\ExistingRequest;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Dcodegroup\ActivityLog\Models\ActivityLog;

class CommentController extends Controller
{
    public function __invoke(ExistingRequest $request)
    {
        $modelClass = $request->input('modelClass');
        $modelId = $request->input('modelId');
        $model = $modelClass::find($modelId);
        $baseClass = class_basename($modelClass);
        if ($request->filled('comment')) {
            ActivityLog::query()->create([
                'activitiable_type' => $modelClass,
                'activitiable_id' => $modelId,
                'description' => "add a comment" . ' in ' . $baseClass . ' history </br>'
                    . $request->input('comment'),
            ]);
        }

        return new ActivityLogCollection($model->activityLogs()->orderByDesc('created_at')->get());
    }
}
