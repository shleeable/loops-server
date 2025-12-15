<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('video_id');
            $table->enum('feedback_type', ['like', 'dislike', 'not_interested', 'hide_creator']);
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['profile_id', 'video_id', 'feedback_type']);
            $table->index(['profile_id', 'feedback_type']);
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
        Schema::dropIfExists('feed_feedback');
    }
};