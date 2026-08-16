<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->unsignedTinyInteger('publish_state')->default(2);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('publishing_at')->nullable();
            $table->string('publish_failure_reason', 191)->nullable();

            $table->index(['publish_state', 'scheduled_at'], 'videos_publish_state_scheduled_at_idx');
            $table->index(['profile_id', 'publish_state'], 'videos_profile_publish_state_idx');
        });

        DB::table('videos')
            ->select('id', 'created_at')
            ->where('status', 2)
            ->whereNull('ap_published_at')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('videos')
                        ->where('id', $row->id)
                        ->update(['ap_published_at' => $row->federated_at ?? $row->created_at]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_publish_state_scheduled_at_idx');
            $table->dropIndex('videos_profile_publish_state_idx');
            $table->dropColumn(['publish_state', 'scheduled_at', 'publishing_at', 'publish_failure_reason']);
        });
    }
};
