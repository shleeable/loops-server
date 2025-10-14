<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('type', 50);
            $table->string('object_type', 50)->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->string('activity_id', 255);
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('raw_activity')->nullable();
            $table->json('payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->unique('activity_id', 'activities_activity_id_unique');
            $table->index('profile_id', 'activities_profile_id_index');
            $table->index('type', 'activities_type_index');
            $table->index(['processed', 'created_at'], 'activities_processed_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
