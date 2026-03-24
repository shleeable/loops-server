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
        Schema::table('starter_kit_accounts', function (Blueprint $table) {
            $table->string('remote_object_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starter_kit_accounts', function (Blueprint $table) {
            $table->dropColumn('remote_object_id');
        });
    }
};
