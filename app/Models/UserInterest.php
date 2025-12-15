<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInterest extends Model
{
    protected $fillable = [
        'profile_id',
        'interest_type',
        'interest_value',
        'score',
        'interaction_count',
        'last_interaction_at',
    ];

    protected $casts = [
        'score' => 'decimal:4',
        'last_interaction_at' => 'datetime',
    ];

    const TYPE_HASHTAG = 'hashtag';

    const TYPE_CATEGORY = 'category';

    const TYPE_CREATOR = 'creator';

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Increment the interest score based on interaction
     */
    public function recordInteraction(float $weight = 1.0): void
    {
        $this->increment('interaction_count');
        $this->score = min(100, $this->score + ($weight * 2));
        $this->last_interaction_at = now();
        $this->save();
    }

    /**
     * Decay the interest score over time
     */
    public function decay(float $decayRate = 0.95): void
    {
        $this->score *= $decayRate;
        $this->save();
    }
}
