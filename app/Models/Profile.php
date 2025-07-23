<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Observers\ProfileObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ProfileObserver::class])]
class Profile extends Model
{
    use HasFactory, HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'username', 'name', 'avatar', 'followers', 'following'];

    protected $guarded = [];

    protected $casts = [
        'links' => 'array',
    ];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function deepLink()
    {
        $q = http_build_query([
            'avatar' => $this->avatar,
            'username' => $this->username,
            'name' => $this->name,
            'src' => 'api',
        ]);

        return 'pfloops://profile/id/'.$this->id.'?'.$q;
    }

    public function getPublicUrl()
    {
        return url('/@'.$this->username);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
