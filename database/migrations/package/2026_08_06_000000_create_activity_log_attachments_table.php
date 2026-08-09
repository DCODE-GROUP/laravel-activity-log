<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_log_attachments')) {
            return;
        }

        Schema::create('activity_log_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('activity_log_id');
            $table->unsignedBigInteger('attachment_id');
            $table->timestamps();

            $table->foreign('activity_log_id')
                ->references('id')
                ->on('activity_logs')
                ->cascadeOnDelete();
            $table->index('attachment_id');
            $table->unique(['activity_log_id', 'attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_attachments');
    }
};
