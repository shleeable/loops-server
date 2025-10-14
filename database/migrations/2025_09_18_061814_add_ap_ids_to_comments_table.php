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
            $table->string('ap_id')->nullable()->unique()->index();
            $table->string('remote_url')->nullable();
        });

        Schema::table('comment_replies', function (Blueprint $table) {
            $table->string('ap_id')->nullable()->unique()->index();
            $table->string('remote_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('remote_url');
            $table->dropColumn('ap_id');
        });

        Schema::table('comment_replies', function (Blueprint $table) {
            $table->dropColumn('remote_url');
            $table->dropColumn('ap_id');
        });
    }
};
