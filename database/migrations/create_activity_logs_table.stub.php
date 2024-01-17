<?php

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Models\CommunicationLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration
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

            $table->foreignIdFor(CommunicationLog::class)->after('id')->nullable();
            $table->foreign('communication_log_id')->references('id')->on('communication_logs');

            $table->json('values')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('form_id');
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
}
