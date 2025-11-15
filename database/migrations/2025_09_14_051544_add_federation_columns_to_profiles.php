<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'domain')) {
                $table->string('domain', 255)->nullable()->after('uri');
            }

            $table->text('inbox_url')->nullable()->after('public_key');
            $table->text('outbox_url')->nullable()->after('inbox_url');
            $table->text('following_url')->nullable()->after('outbox_url');
            $table->text('followers_url')->nullable()->after('following_url');
            $table->text('shared_inbox_url')->nullable()->after('outbox_url');

            if (! Schema::hasColumn('profiles', 'last_fetched_at')) {
                $table->timestamp('last_fetched_at')->nullable();
            }
            $table->timestamp('last_fetch_failure_at')->nullable();
            $table->timestamp('last_delivery_failure_at')->nullable();

            $table->unsignedInteger('fetch_failure_count')->default(0);
            $table->unsignedInteger('delivery_failure_count')->default(0);

            $table->index('domain', 'profiles_domain_index');
            $table->index('last_fetched_at', 'profiles_last_fetched_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('profiles_domain_index');
            $table->dropIndex('profiles_last_fetched_at_index');

            $table->dropColumn([
                'domain',
                'inbox_url',
                'outbox_url',
                'following_url',
                'followers_url',
                'shared_inbox_url',
                'last_fetched_at',
                'last_fetch_failure_at',
                'last_delivery_failure_at',
                'fetch_failure_count',
                'delivery_failure_count',
            ]);
        });
    }
};
