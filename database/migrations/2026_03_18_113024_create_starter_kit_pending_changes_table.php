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
        Schema::create('starter_kit_pending_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('starter_kit_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('profile_id')->index();
            $table->json('original');
            $table->json('changeset');
            $table->enum('status', ['pending', 'in_review', 'applied', 'rejected'])->default('pending')->index();
            $table->boolean('bundled_with_kit_review')->default(false);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->index(['bundled_with_kit_review', 'status']);
            $table->index(['starter_kit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starter_kit_pending_changes');
    }
};
