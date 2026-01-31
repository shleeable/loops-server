<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->tinyInteger('actor_state')->unsigned()->default(1)->after('profile_id');
            $table->index(['user_id', 'actor_state', 'type', 'created_at'], 'notifications_user_actor_type_created_idx');
        });

        DB::statement('
            UPDATE notifications n
            INNER JOIN profiles p ON n.profile_id = p.id
            SET n.actor_state = CASE
                WHEN p.status = 6 THEN 6
                WHEN p.status = 7 THEN 7
                WHEN p.status = 8 THEN 8
                ELSE 1
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasIndex('notifications', 'notifications_user_actor_type_created_idx')) {
                $table->dropIndex('notifications_user_actor_type_created_idx');
            }
            $table->dropColumn('actor_state');
        });
    }
};
