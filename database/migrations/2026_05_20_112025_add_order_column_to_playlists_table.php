<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->unsignedInteger('order_column')->default(0)->after('videos_count');
            $table->index(['profile_id', 'order_column']);
        });

        DB::statement('
            UPDATE playlists p
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY profile_id ORDER BY created_at) as rn
                FROM playlists
            ) ranked ON p.id = ranked.id
            SET p.order_column = ranked.rn
        ');
    }

    public function down(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropIndex(['profile_id', 'order_column']);
            $table->dropColumn('order_column');
        });
    }
};
