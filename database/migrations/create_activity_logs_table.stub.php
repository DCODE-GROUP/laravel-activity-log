<?php

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('activitiable');
            $table->string('type')->default(ActivityLog::TYPE_GENERAL);
            $table->foreignIdFor(CommunicationLog::class)->nullable();
            $table->foreign('communication_log_id')->references('id')->on('communication_logs');
            $table->text('description')->nullable();
            $table->text('meta')->nullable();
            $table->json('diff')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on(config('activity-log.user_table'))->onDelete('cascade');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on(config('activity-log.user_table'))->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
