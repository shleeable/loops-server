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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->unique()->index();
            $table->unsignedBigInteger('total_count')->default(0)->index();
            $table->text('admin_notes')->nullable();
            $table->text('public_description')->nullable();
            $table->unsignedTinyInteger('topic_rank')->default(50)->index();
            $table->json('subtopics')->nullable();
            $table->json('related_topics')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_url')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
