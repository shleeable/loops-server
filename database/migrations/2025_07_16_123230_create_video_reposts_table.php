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
        Schema::create('video_reposts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->index();
            $table->unsignedBigInteger('video_id')->index();
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->foreign('video_id')->references('id')->on('videos')->cascadeOnDelete();
            $table->unique(['profile_id', 'video_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_reposts');
    }
};
