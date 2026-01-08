<?php

namespace App\Models;

use App\Observers\UserAppPreferenceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UserAppPreferenceObserver::class])]

/**
 * @property int $id
 * @property int $user_id
 * @property bool $autoplay_videos
 * @property bool $loop_videos
 * @property bool $data_saver_mode
 * @property string $default_feed
 * @property bool $hide_for_you_feed
 * @property bool $mute_on_open
 * @property string $lang
 * @property bool $auto_expand_cw
 * @property string $appearance
 * @property bool $reduce_motion
 * @property bool $high_contrast
 * @property array<array-key, mixed>|null $extra_settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereAppearance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereAutoExpandCw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereAutoplayVideos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereDataSaverMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereDefaultFeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereExtraSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereHideForYouFeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereHighContrast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereLoopVideos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereMuteOnOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereReduceMotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAppPreference whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserAppPreference extends Model
{
    protected $fillable = [
        'user_id',
        'autoplay_videos',
        'loop_videos',
        // 'data_saver_mode',
        'default_feed',
        'hide_for_you_feed',
        'mute_on_open',
        // 'lang',
        'auto_expand_cw',
        'appearance',
        // 'reduce_motion',
        // 'high_contrast',
        // 'extra_settings',
    ];

    protected $casts = [
        'autoplay_videos' => 'boolean',
        'loop_videos' => 'boolean',
        'data_saver_mode' => 'boolean',
        'hide_for_you_feed' => 'boolean',
        'mute_on_open' => 'boolean',
        'auto_expand_cw' => 'boolean',
        'reduce_motion' => 'boolean',
        'high_contrast' => 'boolean',
        'extra_settings' => 'array',
    ];

    public $visible = [
        'autoplay_videos',
        'loop_videos',
        // 'data_saver_mode',
        'default_feed',
        'hide_for_you_feed',
        'mute_on_open',
        // 'lang',
        'auto_expand_cw',
        'appearance',
        // 'reduce_motion',
        // 'high_contrast',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
