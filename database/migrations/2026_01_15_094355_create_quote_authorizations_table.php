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
        Schema::create('quote_authorizations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('quotable_id')->index();
            $table->string('quotable_type')->index();
            $table->unsignedBigInteger('quoted_profile_id')->nullable();
            $table->unsignedBigInteger('quoter_profile_id')->nullable();
            $table->string('quote_post_url');
            $table->timestamps();

            $table->index(['quotable_type', 'quotable_id']);

            $table->index('quote_post_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_authorizations');
    }
};
