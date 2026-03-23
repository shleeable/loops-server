<?php

use App\Models\Profile;
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
        Schema::create('starter_kits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('remote_url')->nullable()->unique();
            $table->string('remote_object_url')->nullable()->unique();
            $table->string('header_path')->nullable();
            $table->string('header_url')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('icon_url')->nullable();
            $table->unsignedBigInteger('profile_id')->nullable()->index();
            $table->unsignedBigInteger('uses')->default(0);
            $table->unsignedBigInteger('total_reach')->default(0);
            $table->unsignedTinyInteger('total_accounts')->default(0);
            $table->unsignedTinyInteger('approved_accounts')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('previous_status')->default(0);
            $table->unsignedTinyInteger('visibility')->default(1);
            $table->unsignedBigInteger('topic_hashtag_id')->nullable()->index();
            $table->boolean('can_federate')->default(false)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_discoverable')->default(false);
            $table->boolean('is_local')->default(false);
            $table->boolean('is_loops_only')->default(false);
            $table->text('admin_note')->nullable();
            $table->text('disabled_message')->nullable();
            $table->text('rejected_message')->nullable();
            $table->timestamp('observatory_submitted_at')->nullable();
            $table->timestamp('admin_approved_at')->nullable();
            $table->timestamp('delete_after')->nullable();
            $table->timestamps();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('starter_kit_state')->default(0);
        });

        Profile::where('local', true)->update(['starter_kit_state' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('starter_kit_state');
        });

        Schema::dropIfExists('starter_kits');
    }
};
