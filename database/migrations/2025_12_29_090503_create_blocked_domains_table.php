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
        Schema::create('blocked_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->boolean('is_subdomain')->default(false);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index(['domain', 'is_subdomain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_domains');
    }
};
