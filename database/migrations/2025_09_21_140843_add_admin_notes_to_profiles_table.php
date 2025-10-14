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
        Schema::table('profiles', function (Blueprint $table) {
            $table->text('admin_notes')->nullable();
            $table->boolean('can_upload')->default(true);
            $table->boolean('can_follow')->default(true);
            $table->boolean('can_comment')->default(true);
            $table->boolean('can_like')->default(true);
            $table->boolean('can_share')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('admin_notes');
            $table->dropColumn('can_upload');
            $table->dropColumn('can_follow');
            $table->dropColumn('can_comment');
            $table->dropColumn('can_like');
            $table->dropColumn('can_share');
        });
    }
};
