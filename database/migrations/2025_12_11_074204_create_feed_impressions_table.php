<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_impressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('video_id');
            $table->timestamp('viewed_at')->useCurrent();
            $table->smallInteger('watch_duration')->unsigned()->default(0);
            $table->boolean('completed')->default(false);

            $table->unique(['profile_id', 'video_id']);
            $table->index(['profile_id', 'viewed_at']);
            $table->index('video_id');

            $table->foreign('profile_id')
                ->references('id')
                ->on('profiles')
                ->onDelete('cascade');

            $table->foreign('video_id')
                ->references('id')
                ->on('videos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_impressions');
    }
};
