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
            $table->boolean('hide_ai')->default(false);
            $table->boolean('hide_sensitive')->default(false);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('hide_ai')->default(false);
            $table->boolean('hide_sensitive')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hide_ai');
            $table->dropColumn('hide_sensitive');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('hide_ai');
            $table->dropColumn('hide_sensitive');
        });
    }
};
