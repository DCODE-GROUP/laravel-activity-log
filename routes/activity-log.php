<?php

use Dcodegroup\ActivityLog\Http\Controllers\API\ActivityLogController;
use Dcodegroup\ActivityLog\Http\Controllers\API\CommentController;
use Dcodegroup\ActivityLog\Http\Controllers\API\DeleteCommentController;
use Dcodegroup\ActivityLog\Http\Controllers\API\EditCommentController;
use Dcodegroup\ActivityLog\Http\Controllers\API\FilterController;
use Dcodegroup\ActivityLog\Http\Controllers\API\ReadEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/'.config('activity-log.route_path'), ActivityLogController::class)->name(config('activity-log.route_name'));
Route::post('/'.config('activity-log.route_path').'/comments', CommentController::class)->name(config('activity-log.route_name').'.comment');
Route::post('/'.config('activity-log.route_path').'/resend-communication/{communicationLog}', ResendCommunicationController::class)->name(config('activity-log.route_name').'.resend-communication');
Route::patch('/'.config('activity-log.route_path').'/comments/{comment}', EditCommentController::class)->name(config('activity-log.route_name').'.comment.edit');
Route::delete('/'.config('activity-log.route_path').'/comment/{comment}', DeleteCommentController::class)->name(config('activity-log.route_name').'.comment.delete');
Route::get('/'.config('activity-log.route_path').'/filters', FilterController::class)->name(config('activity-log.route_name').'.filters');
Route::get('/'.config('activity-log.route_path').'/filters/facets/{facet}', [FilterController::class, 'search'])->name(config('activity-log.route_name').'.facets.search');
Route::get('/'.config('activity-log.route_path').'/{activity_log}/read-email', ReadEmailController::class)->withoutMiddleware('auth')->name(config('activity-log.route_name').'.read-email');
