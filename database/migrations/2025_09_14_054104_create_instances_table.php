<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_count')->nullable();
            $table->unsignedBigInteger('video_count')->nullable();
            $table->unsignedBigInteger('comment_count')->nullable();
            $table->unsignedBigInteger('reply_count')->nullable();
            $table->unsignedBigInteger('follower_count')->nullable();
            $table->unsignedBigInteger('report_count')->nullable();
            $table->string('software', 255)->nullable();
            $table->string('version', 255)->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_silenced')->default(false);
            $table->unsignedTinyInteger('federation_state')->default(5);
            $table->text('admin_notes')->nullable();
            $table->boolean('allow_video_posts')->default(false);
            $table->boolean('allow_videos_in_fyf')->default(false);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamp('version_last_checked_at')->nullable();
            $table->timestamp('instance_last_crawled_at')->nullable();
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();

            $table->unique('domain', 'instances_domain_unique');
            $table->index('is_blocked', 'instances_is_blocked_index');
            $table->index('federation_state', 'instances_federation_state_index');
            $table->index('allow_video_posts', 'instances_allow_video_posts_index');
            $table->index('allow_videos_in_fyf', 'instances_allow_videos_in_fyf_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instances');
    }
};
