<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $video_id
 * @property string|null $topics
 * @property string|null $subtopics
 * @property string|null $content_filters
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereContentFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereSubtopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereTopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoTopic whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class VideoTopic extends Model
{
    use HasFactory;
}
