<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->tinyInteger('profile_is_local')->default(true)->after('following_is_local');
            $table->index('profile_is_local', 'followers_profile_is_local_index');
        });
    }

    public function down(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->dropIndex('followers_profile_is_local_index');
            $table->dropColumn([
                'profile_is_local',
            ]);
        });
    }
};
