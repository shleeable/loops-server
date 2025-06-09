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
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profile_id')->nullable()->index();
            $table->string('vid')->nullable();
            $table->text('vid_optimized')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->index();
            $table->unsignedTinyInteger('duration')->nullable();
            $table->unsignedInteger('size_kb')->nullable();
            $table->text('caption')->nullable();
            $table->string('category', 50)->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_adult')->default(false);
            $table->boolean('has_audio')->default(true);
            $table->boolean('has_thumb')->default(false);
            $table->boolean('has_processed')->default(false);
            $table->boolean('is_approved')->default(false)->index();
            $table->json('features')->nullable();
            $table->json('media_metadata')->nullable();
            $table->unsignedTinyInteger('comment_state')->default(4);
            $table->string('cw_title')->nullable();
            $table->text('cw_body')->nullable();
            $table->string('sha512_hash')->nullable()->index();
            $table->boolean('has_hls')->default(false);
            $table->boolean('can_download')->default(false);
            $table->boolean('can_duet')->default(false);
            $table->boolean('can_stitch')->default(false);
            $table->boolean('is_pinned')->default(false)->index();
            $table->unsignedTinyInteger('pinned_order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
