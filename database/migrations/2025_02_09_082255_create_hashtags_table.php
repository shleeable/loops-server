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
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->unique();
            $table->string('name_normalized', 191)->unique();
            $table->boolean('can_trend')->default(false)->index();
            $table->boolean('can_search')->default(true);
            $table->boolean('can_autolink')->default(true);
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hashtags');
    }
};
