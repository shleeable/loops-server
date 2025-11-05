<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            UPDATE followers
            INNER JOIN profiles ON profiles.id = followers.profile_id
            SET followers.profile_is_local = CASE
                WHEN profiles.domain IS NULL OR profiles.user_id IS NOT NULL THEN 1
                ELSE 0
            END
        ');

        DB::statement('
            UPDATE followers
            INNER JOIN profiles ON profiles.id = followers.following_id
            SET followers.following_is_local = CASE
                WHEN profiles.domain IS NULL OR profiles.user_id IS NOT NULL THEN 1
                ELSE 0
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('followers')->update([
            'profile_is_local' => true,
            'following_is_local' => true,
        ]);
    }
};
