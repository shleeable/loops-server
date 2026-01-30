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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('processing_status')->default('pending'); // pending, processing, completed, failed
            $table->text('processing_error')->nullable();
            $table->timestamp('processing_failed_at')->nullable();
        });

        DB::table('videos')->where('status', 2)->update(['processing_status' => 'completed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('processing_status');
            $table->dropColumn('processing_error');
            $table->dropColumn('processing_failed_at');
        });
    }
};
