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
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('username')->unique()->index();
            $table->string('domain')->nullable();
            $table->text('bio')->nullable();
            $table->text('avatar')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('followers')->default(0);
            $table->unsignedInteger('following')->default(0);
            $table->unsignedInteger('video_count')->default(0);
            $table->boolean('local')->default(true)->index();
            $table->string('uri')->nullable()->unique();
            $table->json('links')->nullable();
            $table->text('public_key')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
