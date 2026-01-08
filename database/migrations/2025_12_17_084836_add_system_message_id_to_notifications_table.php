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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('system_message_id')->nullable()->after('comment_reply_id');

            $table->foreign('system_message_id')
                ->references('id')
                ->on('system_messages')
                ->onDelete('cascade');

            $table->index('system_message_id');
            $table->unique(['user_id', 'system_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('notifications_user_id_system_message_id_unique');
            $table->dropForeign(['system_message_id']);
            $table->dropIndex(['system_message_id']);
            $table->dropColumn('system_message_id');
        });
    }
};
