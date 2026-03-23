<?php

namespace App\Services;

use App\Models\PrivateMediaToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaTokenService
{
    /**
     * Create (or replace) a token for a given profile + path.
     *
     * Pass a context string like 'starter_kit_pending.banner' so you can
     * bulk-delete tokens when a pending change is resolved.
     */
    public function create(
        int $profileId,
        string $path,
        string $context = '',
        string $disk = 's3',
        ?int $ttlHours = null,
    ): PrivateMediaToken {
        $mime = rescue(
            fn () => Storage::disk($disk)->mimeType($path),
            'application/octet-stream'
        );

        return PrivateMediaToken::create([
            'profile_id' => $profileId,
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $mime,
            'context' => $context,
            'expires_at' => $ttlHours ? now()->addHours($ttlHours) : null,
        ]);
    }

    /**
     * Resolve and validate a token — throws HTTP exceptions on failure.
     * On success, touches last_accessed_at and returns the token.
     */
    public function resolve(string $tokenId, int $profileId, bool $isAdmin): PrivateMediaToken
    {
        $token = PrivateMediaToken::findOrFail($tokenId);

        abort_if($token->isExpired(), 410, 'This preview link has expired');
        if (! $isAdmin) {
            abort_unless($token->isOwnedBy($profileId), 403, 'Forbidden');
        }

        $token->updateQuietly(['last_accessed_at' => now()]);

        return $token;
    }

    /**
     * Stream the file from the token's disk to the response.
     */
    public function stream(PrivateMediaToken $token): StreamedResponse
    {
        $disk = Storage::disk($token->disk);

        abort_unless($disk->exists($token->path), 404, 'Media not found');

        $size = $disk->size($token->path);

        return response()->stream(
            function () use ($disk, $token) {
                $stream = $disk->readStream($token->path);

                abort_unless(is_resource($stream), 500, 'Failed to open media stream');

                while (! feof($stream)) {
                    echo fread($stream, 64 * 1024);
                    flush();
                }

                fclose($stream);
            },
            200,
            [
                'Content-Type' => $token->mime_type,
                'Content-Length' => $size,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Revoke all tokens for a given context — call this when a pending
     * change is approved, rejected, or deleted.
     */
    public function revokeFor(Model $model): int
    {
        return PrivateMediaToken::whereMorphedTo('tokenable', $model)->delete();
    }

    public function attach(PrivateMediaToken $token, Model $for): PrivateMediaToken
    {
        $token->update([
            'tokenable_type' => $for->getMorphClass(),
            'tokenable_id' => $for->getKey(),
        ]);

        return $token;
    }

    /**
     * Prune all expired tokens — wire this up to a scheduled command.
     */
    public function pruneExpired(): int
    {
        return PrivateMediaToken::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
