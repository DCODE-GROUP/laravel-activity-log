<?php

namespace Dcodegroup\ActivityLog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Dcodegroup\ActivityLog\Resources\ActivityLog as ActivityLogResource;

class ActivityLogCollection extends ResourceCollection
{
    /**
     * Ensure each item is transformed using ActivityLog resource
     */
    public $collects = ActivityLogResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
