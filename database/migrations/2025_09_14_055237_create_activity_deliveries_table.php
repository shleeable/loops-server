<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->text('inbox_url');
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('attempts')->default(0);
            $table->enum('status', ['pending', 'processing', 'delivered', 'failed'])->default('pending');
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['status', 'next_retry_at'], 'deliveries_status_next_retry_index');
            $table->index('activity_id', 'deliveries_activity_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_deliveries');
    }
};
