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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_profile_id')->nullable();
            $table->unsignedBigInteger('reported_profile_id')->nullable();
            $table->unsignedBigInteger('reported_video_id')->nullable();
            $table->unsignedBigInteger('reported_comment_id')->nullable();
            $table->string('report_type')->default('spam');
            $table->json('metadata')->nullable();
            $table->boolean('admin_seen')->default(false)->index();
            $table->boolean('handled')->default(false)->index();
            $table->text('admin_notes')->nullable();
            $table->foreign('reporter_profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->foreign('reported_profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->foreign('reported_video_id')->references('id')->on('videos')->cascadeOnDelete();
            $table->foreign('reported_comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->timestamp('admin_remind_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
