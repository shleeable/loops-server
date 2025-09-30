<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string $slug
 * @property int $total_count
 * @property string|null $admin_notes
 * @property string|null $public_description
 * @property int $topic_rank
 * @property string|null $subtopics
 * @property string|null $related_topics
 * @property string|null $icon
 * @property string|null $icon_url
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic wherePublicDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereRelatedTopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereSubtopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereTopicRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topic whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Topic extends Model
{
    use HasFactory;
}
