<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curated_applications', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('email')->unique()->index();
            $table->string('username_requested')->nullable();
            $table->text('birthdate_encrypted')->nullable();
            $table->unsignedSmallInteger('age_at_submission')->nullable();
            $table->text('reason');
            $table->string('fediverse_account')->nullable();
            $table->json('custom_answers')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('status_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('ip_hash')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email_verification_token')->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curated_applications');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
