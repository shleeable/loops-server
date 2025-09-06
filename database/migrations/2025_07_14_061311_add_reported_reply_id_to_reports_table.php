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
            $table->unsignedBigInteger('reported_comment_reply_id')->nullable()->index()->after('reported_comment_id');
            $table->foreign('reported_comment_reply_id')->references('id')->on('comment_replies')->cascadeOnDelete();
            $table->text('user_message')->nullable()->after('handled');
            $table->boolean('is_remote')->default(false)->after('handled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'reported_comment_reply_id')) {
                $table->dropForeign('reported_comment_reply_id');
                $table->dropColumn('reported_comment_reply_id');
            }

            if (Schema::hasColumn('reports', 'user_message')) {
                $table->dropColumn('user_message');
            }

            if (Schema::hasColumn('reports', 'is_remote')) {
                $table->dropColumn('is_remote');
            }
        });
    }
};
