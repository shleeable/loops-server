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
        DB::statement("ALTER TABLE comments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE comment_replies MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active'");

        DB::statement("
            UPDATE comments c
            INNER JOIN profiles p ON c.profile_id = p.id
            SET c.status = CASE
                WHEN p.status = 6 THEN 'account_pending_deletion'
                WHEN p.status = 7 THEN 'account_disabled'
                WHEN p.status = 8 THEN 'account_pending_deletion'
                ELSE 'account_disabled'
            END
            WHERE p.status != 1
            AND c.status = 'active'
        ");

        DB::statement("
            UPDATE comment_replies cr
            INNER JOIN profiles p ON cr.profile_id = p.id
            SET cr.status = CASE
                WHEN p.status = 6 THEN 'account_pending_deletion'
                WHEN p.status = 7 THEN 'account_disabled'
                WHEN p.status = 8 THEN 'account_pending_deletion'
                ELSE 'account_disabled'
            END
            WHERE p.status != 1
            AND cr.status = 'active'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE comments MODIFY COLUMN status ENUM('active', 'deleted_by_user', 'deleted_by_admin', 'account_pending_deletion', 'account_disabled') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE comment_replies MODIFY COLUMN status ENUM('active', 'deleted_by_user', 'deleted_by_admin', 'account_pending_deletion', 'account_disabled') NOT NULL DEFAULT 'active'");
    }
};
