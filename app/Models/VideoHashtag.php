<?php

namespace App\Models;

use App\Observers\VideoHashtagObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([VideoHashtagObserver::class])]
/**
 * @property int $id
 * @property int $video_id
 * @property int $hashtag_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag whereHashtagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoHashtag whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class VideoHashtag extends Model
{
    //
}
