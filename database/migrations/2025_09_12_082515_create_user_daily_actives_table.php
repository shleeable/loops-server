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
        Schema::create('user_daily_actives', function (Blueprint $table) {
            $table->date('day');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['day', 'user_id']);
            $table->index(['day', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_actives');
    }
};
