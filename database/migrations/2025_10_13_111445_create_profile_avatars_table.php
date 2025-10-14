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
        Schema::create('profile_avatars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->unique();
            $table->text('remote_url')->nullable();
            $table->string('path')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->boolean('skip_refetch')->default(false);
            $table->boolean('is_invalid')->default(false);
            $table->text('last_error')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->index(['last_fetched_at', 'is_invalid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_avatars');
    }
};
