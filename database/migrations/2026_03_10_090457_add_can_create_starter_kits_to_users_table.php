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
            $table->boolean('can_share')->default(true)->after('can_follow');
            $table->boolean('can_create_starter_kits')->default(true)->after('can_share');
            $table->boolean('can_use_starter_kits')->default(true)->after('can_create_starter_kits');
            $table->boolean('can_report')->default(true)->after('can_use_starter_kits');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('can_create_starter_kits')->default(true)->after('can_share');
            $table->boolean('can_use_starter_kits')->default(true)->after('can_create_starter_kits');
            $table->boolean('can_report')->default(true)->after('can_create_starter_kits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_share');
            $table->dropColumn('can_create_starter_kits');
            $table->dropColumn('can_use_starter_kits');
            $table->dropColumn('can_report');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('can_create_starter_kits');
            $table->dropColumn('can_use_starter_kits');
            $table->dropColumn('can_report');
        });
    }
};
