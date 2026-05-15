<?php

use App\Models\Video;
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
            $table->boolean('can_embed')->default(false)->after('can_stitch');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_embed')->default(true)->after('can_report');
        });

        Video::where('is_local', true)
            ->where('can_download', true)
            ->lazyById(200, column: 'id')
            ->each->updateQuietly(['can_embed' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('can_embed');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_embed');
        });
    }
};
