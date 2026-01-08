<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->string('interest_type');
            $table->string('interest_value');
            $table->decimal('score', 8, 4)->default(0);
            $table->unsignedInteger('interaction_count')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'interest_type', 'interest_value']);
            $table->index(['profile_id', 'score']);
            $table->index(['profile_id', 'interest_type']);

            $table->foreign('profile_id')
                ->references('id')
                ->on('profiles')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interests');
    }
};
