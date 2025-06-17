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
        Schema::create('user_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->unique(['profile_id', 'account_id']);
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_filters');
    }
};
