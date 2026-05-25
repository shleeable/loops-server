<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('playlist_video', function (Blueprint $table) {
            $table->index('video_id');
            $table->dropUnique(['video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('playlist_video', function (Blueprint $table) {
            $table->dropIndex(['video_id']);
            $table->unique('video_id');
        });
    }
};
