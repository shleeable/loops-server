<?php

use App\Models\Notification;
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
        Schema::create('system_messages', function (Blueprint $table) {
            $table->id();
            $table->string('key_id')->unique()->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable()->unique()->index();
            $table->text('body');
            $table->unsignedTinyInteger('type')->default(8);
            $table->string('link')->nullable();
            $table->text('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_public_link')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('notifications_generated_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Notification::whereIn('type', [8, 9, 10])->delete();
        Schema::dropIfExists('system_messages');
    }
};
