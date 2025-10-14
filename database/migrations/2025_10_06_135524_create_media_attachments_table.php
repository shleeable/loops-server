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
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profile_id')->index();
            $table->morphs('attachable');
            $table->string('remote_url')->nullable();
            $table->string('local_path')->nullable();
            $table->string('cache_url')->nullable();
            $table->string('storage_driver')->nullable();
            $table->string('mime_type');
            $table->string('blurhash')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->unsignedTinyInteger('visibility')->default(1)->index();
            $table->boolean('has_cached')->default(0)->index();
            $table->boolean('is_sensitive')->default(0);
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
