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
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('shares')->default(0)->after('likes');
        });

        Schema::table('comment_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('shares')->default(0)->after('likes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('shares');
        });

        Schema::table('comment_replies', function (Blueprint $table) {
            $table->dropColumn('shares');
        });
    }
};
