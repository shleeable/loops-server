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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_playlist')->default(true);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('can_playlist')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_playlist');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('can_playlist');
        });
    }
};
