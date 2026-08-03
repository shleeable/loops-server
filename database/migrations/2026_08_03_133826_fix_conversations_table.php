<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('context_uri', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tooLong = DB::table('conversations')
            ->whereRaw('LENGTH(context_uri) > 64')
            ->count();

        if ($tooLong > 0) {
            throw new RuntimeException(
                "Refusing to narrow conversations.context_uri: {$tooLong} row(s) would be truncated."
            );
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('context_uri', 64)->nullable()->change();
        });
    }
};
