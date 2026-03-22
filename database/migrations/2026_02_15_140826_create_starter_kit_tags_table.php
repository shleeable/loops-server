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
        Schema::create('starter_kit_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('starter_kit_id')->index();
            $table->unsignedBigInteger('hashtag_id')->index();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('order')->default(0);
            $table->foreign('starter_kit_id')->references('id')->on('starter_kits')->onDelete('cascade');
            $table->unique(['hashtag_id', 'starter_kit_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_kit_tags');
    }
};
