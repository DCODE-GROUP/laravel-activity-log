<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_log_reactions')) {
            return;
        }

        Schema::create('activity_log_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('emoji', 10)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamps();

            $table->index('activity_log_id');
            $table->index('user_id');
            $table->unique(['activity_log_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_reactions');
    }
};
