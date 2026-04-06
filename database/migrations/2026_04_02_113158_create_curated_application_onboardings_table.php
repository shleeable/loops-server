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
        Schema::create('curated_application_onboardings', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->foreignId('application_id')->constrained('curated_applications')->cascadeOnDelete();
            $table->string('email')->unique()->nullable();
            $table->string('username_requested')->nullable();
            $table->string('magic_key')->nullable();
            $table->timestamp('expires_after')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curated_application_onboardings');
    }
};
