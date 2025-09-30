<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $video_id
 * @property string|null $sha512_hash
 * @property int $can_reshare
 * @property string|null $storage_path
 * @property int $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereCanReshare($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereSha512Hash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class Sound extends Model
{
    use HasFactory;
}
