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
        Schema::create('email_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('old_email');
            $table->string('new_email');
            $table->string('token', 255);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['token', 'expires_at']);
            $table->index(['user_id', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_changes');

        if (Schema::hasColumn('users', 'email_verification_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verification_token');
            });
        }
    }
};
