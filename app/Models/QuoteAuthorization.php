<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QuoteAuthorization extends Model
{
    use HasFactory, HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quoted_profile_id',
        'quoter_profile_id',
        'quotable_id',
        'quotable_type',
        'quote_post_url',
    ];

    /**
     * Get the parent quotable model (Video, Comment, or CommentReply).
     */
    public function quotable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the ActivityPub URL for this authorization.
     */
    public function getApUrlAttribute(): string
    {
        return url("/ap/users/{$this->quoted_profile_id}/quote_authorizations/{$this->id}");
    }

    /**
     * Generate the ActivityPub representation of this authorization.
     */
    public function toActivityPub(): array
    {
        /** @var \App\Models\Video|\App\Models\Comment|\App\Models\CommentReply $quotable */
        $quotable = $this->quotable;

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                [
                    'QuoteAuthorization' => 'https://w3id.org/fep/044f#QuoteAuthorization',
                    'gts' => 'https://gotosocial.org/ns#',
                    'interactingObject' => [
                        '@id' => 'gts:interactingObject',
                        '@type' => '@id',
                    ],
                    'interactionTarget' => [
                        '@id' => 'gts:interactionTarget',
                        '@type' => '@id',
                    ],
                ],
            ],
            'type' => 'QuoteAuthorization',
            'id' => $this->ap_url,
            'attributedTo' => $quotable->profile->permalink(),
            'interactingObject' => $this->quote_post_url,
            'interactionTarget' => $quotable->permalink(),
        ];
    }

    /**
     * Create a new authorization for a quotable object.
     */
    public static function createForQuote(Model $quotable, string $quotePostUrl): self
    {
        return static::create([
            'quotable_id' => $quotable->getKey(),
            'quotable_type' => get_class($quotable),
            'quote_post_url' => $quotePostUrl,
        ]);
    }

    /**
     * Find authorization by quote post URL.
     */
    public static function findByQuoteUrl(string $quotePostUrl): ?self
    {
        return static::where('quote_post_url', $quotePostUrl)->first();
    }

    /**
     * Revoke this authorization (soft delete or actual delete).
     */
    public function revoke(): bool
    {
        return $this->delete();
    }
}
