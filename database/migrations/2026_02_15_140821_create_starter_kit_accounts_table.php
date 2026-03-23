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
        Schema::create('starter_kit_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->index();
            $table->unsignedBigInteger('starter_kit_id')->index();
            $table->unsignedTinyInteger('kit_status')->default(0);
            $table->unsignedTinyInteger('order')->default(0);
            $table->string('attestation_url')->nullable();
            $table->boolean('kit_account_local')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unique(['profile_id', 'starter_kit_id']);
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('starter_kit_id')->references('id')->on('starter_kits')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_kit_accounts');
    }
};
