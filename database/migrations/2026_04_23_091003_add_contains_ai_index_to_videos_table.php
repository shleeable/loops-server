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
        Schema::table('videos', function (Blueprint $table) {
            $table->index(
                ['status', 'is_local', 'contains_ai'],
                'videos_local_feed_index'
            );

            $table->index(
                ['status', 'visibility', 'contains_ai', 'created_at'],
                'videos_fyp_feed_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_local_feed_index');
            $table->dropIndex('videos_fyp_feed_index');
        });
    }
};
