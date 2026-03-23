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
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('reported_starter_kit_id')->nullable()->after('reported_hashtag_id');
            $table->foreign('reported_starter_kit_id')->references('id')->on('starter_kits')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['reported_starter_kit_id']);
            $table->dropColumn('reported_starter_kit_id');
        });
    }
};
