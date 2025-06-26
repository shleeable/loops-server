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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->json('value')->nullable();
            $table->string('type')->default('object');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
