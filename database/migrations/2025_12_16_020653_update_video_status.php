<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = User::whereIn('status', [6])->get();
        foreach($users as $user) {
            $user->videos()->whereIn('status', [2])->update(['status' => 6]);
        }

        $users = User::whereIn('status', [7])->get();
        foreach($users as $user) {
            $user->videos()->whereIn('status', [2])->update(['status' => 7]);
        }

        $users = User::whereIn('status', [8])->get();
        foreach($users as $user) {
            $user->videos()->whereIn('status', [2])->update(['status' => 8]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
