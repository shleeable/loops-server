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
        Schema::create('comment_reply_hashtags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reply_id');
            $table->unsignedBigInteger('hashtag_id')->index();
            $table->foreign('reply_id')->references('id')->on('comment_replies')->cascadeOnDelete();
            $table->foreign('hashtag_id')->references('id')->on('hashtags')->cascadeOnDelete();
            $table->unique(['reply_id', 'hashtag_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reply_hashtags');
    }
};
