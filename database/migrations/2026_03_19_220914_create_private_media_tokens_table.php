<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_media_tokens', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->unsignedBigInteger('profile_id')->nullable()->index();
            $table->string('disk', 20)->default('s3');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->nullableMorphs('tokenable');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            $table->foreign('profile_id')
                ->references('id')->on('profiles')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_media_tokens');
    }
};
