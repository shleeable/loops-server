<?php

use App\Models\User;
use App\Models\UserAppPreference;
use App\Services\UserAppPreferencesService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::chunkById(400, function ($users) {
            foreach ($users as $user) {
                app(UserAppPreferencesService::class)->touch($user->id, true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        UserAppPreference::truncate();
    }
};
