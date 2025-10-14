<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->tinyInteger('visibility')
                ->default(1)
                ->after('status');

            $table->boolean('is_local')
                ->default(true)
                ->after('caption');

            $table->string('uri', 500)
                ->nullable()
                ->after('is_local');

            $table->text('remote_media_url')
                ->nullable()
                ->after('uri');

            $table->text('remote_thumb_url')
                ->nullable()
                ->after('remote_media_url');

            $table->text('remote_hls_url')
                ->nullable()
                ->after('remote_thumb_url');

            $table->timestamp('ap_published_at')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->unsignedInteger('fetch_failure_count')->default(0);

            $table->unique('uri', 'videos_uri_unique');
            $table->index('is_local', 'videos_is_local_index');
            $table->index(['is_local', 'status'], 'videos_is_local_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropUnique('videos_uri_unique');
            $table->dropIndex('videos_is_local_index');
            $table->dropIndex('videos_is_local_status_index');

            $table->dropColumn('fetch_failure_count');
            $table->dropColumn('last_fetched_at');
            $table->dropColumn('ap_published_at');
            $table->dropColumn('remote_hls_url');
            $table->dropColumn('remote_thumb_url');
            $table->dropColumn('remote_media_url');
            $table->dropColumn('uri');
            $table->dropColumn('is_local');
            $table->dropColumn('visibility');
        });
    }
};
