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
        Schema::create('video_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->json('topics')->nullable();
            $table->json('subtopics')->nullable();
            $table->json('content_filters')->nullable();
            $table->foreign('video_id')->references('id')->on('videos')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_topics');
    }
};
