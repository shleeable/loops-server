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
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->morphs('mentionable');
            $table->string('username');
            $table->integer('start_index');
            $table->integer('end_index');
            $table->boolean('is_local')->default(false);
            $table->unsignedBigInteger('profile_id')->nullable()->index();
            $table->timestamps();

            $table->index(['mentionable_type', 'mentionable_id'], 'mentions_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
