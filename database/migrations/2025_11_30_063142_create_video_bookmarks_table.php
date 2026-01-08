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
        Schema::create('video_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('video_id');
            $table->timestamps();

            $table->foreign('profile_id')
                ->references('id')
                ->on('profiles')
                ->onDelete('cascade');

            $table->foreign('video_id')
                ->references('id')
                ->on('videos')
                ->onDelete('cascade');

            $table->unique(['profile_id', 'video_id']);
            $table->index(['profile_id', 'created_at']);
            $table->index('video_id');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->unsignedInteger('bookmarks')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_bookmarks');

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('bookmarks');
        });
    }
};
