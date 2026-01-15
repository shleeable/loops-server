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
        Schema::create('admin_invites', function (Blueprint $table) {
            $table->id();
            $table->string('invite_key')->unique()->index();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('autofollow_accounts')->nullable();
            $table->boolean('verify_email')->default(true);
            $table->boolean('email_admin_on_join')->default(false);
            $table->unsignedInteger('max_uses')->default(0);
            $table->unsignedInteger('total_uses')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('users', function( Blueprint $table) {
            $table->unsignedBigInteger('admin_invite_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_invite_id');
        });
        Schema::dropIfExists('admin_invites');
    }
};
