<?php

use Dcodegroup\ActivityLog\Http\Controllers\API\ActivityLogController;
use Dcodegroup\ActivityLog\Http\Controllers\API\CommentController;
use Dcodegroup\ActivityLog\Http\Controllers\API\FilterController;
use Illuminate\Support\Facades\Route;

Route::get('/'.config('activity-log.route_path'), ActivityLogController::class)->name(config('activity-log.route_name'));
Route::post('/'.config('activity-log.route_path').'/comments', CommentController::class)->name(config('activity-log.route_name').'.comment');
Route::get('/'.config('activity-log.route_path').'/filters', FilterController::class)->name(config('activity-log.route_name').'.filters');
Route::get('/'.config('activity-log.route_path').'/filters/facets/{facet}', [FilterController::class, 'search'])->name(config('activity-log.route_name').'.facets.search');
