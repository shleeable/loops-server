<?php

use App\Models\Conversation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_contexts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('context_uri', 255)->unique();
            $table->timestamps();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('seed_participants_hash', 64)
                ->nullable()
                ->index()
                ->after('participants_hash');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('in_reply_to_id');
        });

        Conversation::where('type', Conversation::TYPE_GROUP)
            ->whereNull('seed_participants_hash')
            ->with('participants')
            ->chunkById(200, function ($conversations) {
                foreach ($conversations as $conversation) {
                    $conversation->forceFill([
                        'seed_participants_hash' => Conversation::groupHash(
                            $conversation->participants->pluck('profile_id')->all()
                        ),
                    ])->save();
                }
            });

        Conversation::whereNotNull('context_uri')
            ->chunkById(500, function ($conversations) {
                $rows = $conversations
                    ->filter(fn ($conversation) => strlen($conversation->context_uri) <= 255)
                    ->map(fn ($conversation) => [
                        'conversation_id' => $conversation->id,
                        'context_uri' => $conversation->context_uri,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->values()
                    ->all();

                if ($rows) {
                    DB::table('conversation_contexts')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_contexts');

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('seed_participants_hash');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['in_reply_to_id']);
        });
    }
};
