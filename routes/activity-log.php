<?php

use Dcodegroup\ActivityLog\Controllers\CommentController;
use Dcodegroup\ActivityLog\Controllers\FilterController;
use Dcodegroup\ActivityLog\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

//Route::get('/'.config('activity-log.route_path'), ActivityLogController::class)->name(config('activity-log.route_name'));
Route::post('/'.config('activity-log.route_path') . '/comments', CommentController::class)->name(config('activity-log.route_name') . '.comment');
Route::get('/'.config('activity-log.route_name') . '/filters', FilterController::class)->name(config('activity-log.route_name') . '.filters');
Route::get('/'.config('activity-log.route_name') . '/filters/facets/{facet}', [FilterController::class, 'search'])->name(config('activity-log.route_name') . '.facets.search');