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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('lang', 10)->nullable()->index();
            $table->text('alt_text')->nullable();
            $table->boolean('contains_ai')->default(false);
            $table->boolean('contains_ad')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('lang');
            $table->dropColumn('alt_text');
            $table->dropColumn('contains_ai');
            $table->dropColumn('contains_ad');
        });
    }
};
