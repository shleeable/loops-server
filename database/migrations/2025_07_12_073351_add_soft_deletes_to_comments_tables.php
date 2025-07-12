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
            $table->enum('status', ['active', 'deleted_by_user', 'deleted_by_admin'])->default('active');
            $table->softDeletes();
        });

        Schema::table('comment_replies', function (Blueprint $table) {
            $table->enum('status', ['active', 'deleted_by_user', 'deleted_by_admin'])->default('active');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
