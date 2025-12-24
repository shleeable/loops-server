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
        Schema::create('user_app_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('autoplay_videos')->default(true);
            $table->boolean('loop_videos')->default(true);
            $table->boolean('data_saver_mode')->default(false);
            $table->string('default_feed')->default('local');
            $table->boolean('hide_for_you_feed')->default(false);
            $table->boolean('mute_on_open')->default(false);
            $table->string('lang')->default('en');
            $table->boolean('auto_expand_cw')->default(false);
            $table->enum('appearance', ['light', 'dark', 'system'])->default('light');
            $table->boolean('reduce_motion')->default(false);
            $table->boolean('high_contrast')->default(false);
            $table->json('extra_settings')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_app_preferences');
    }
};
