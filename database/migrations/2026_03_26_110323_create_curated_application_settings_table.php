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
        Schema::create('curated_application_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_age')->default(16)->nullable();
            $table->text('guidelines')->nullable();
            $table->json('questions')->nullable();
            $table->unsignedInteger('auto_expire_after')->default(0);
            $table->unsignedInteger('auto_expire_unverified_after')->default(0);
            $table->text('approval_template')->nullable();
            $table->text('rejection_template')->nullable();
            $table->json('admin_email_send_to')->nullable();
            $table->boolean('send_rejection_email')->default(false);
            $table->json('rejection_reasons')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curated_application_settings');
    }
};
