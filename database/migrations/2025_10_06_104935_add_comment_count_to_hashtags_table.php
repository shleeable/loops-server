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
        Schema::table('hashtags', function (Blueprint $table) {
            $table->unsignedBigInteger('comment_count')->default(0)->after('count');
            $table->unsignedBigInteger('reply_count')->default(0)->after('comment_count');
        });

        Schema::table('comment_hashtags', function (Blueprint $table) {
            $table->unsignedTinyInteger('visibility')->default(1)->after('hashtag_id');
        });

        Schema::table('comment_reply_hashtags', function (Blueprint $table) {
            $table->unsignedTinyInteger('visibility')->default(1)->after('hashtag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hashtags', function (Blueprint $table) {
            $table->dropColumn('comment_count');
            $table->dropColumn('reply_count');
        });

        Schema::table('comment_hashtags', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });

        Schema::table('comment_reply_hashtags', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
