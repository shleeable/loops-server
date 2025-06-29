<?php

namespace App\Models;

use App\Observers\FollowerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([FollowerObserver::class])]
class Follower extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['profile_id', 'following_id'];

    public $visible = ['profile_id', 'following_id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function following()
    {
        return $this->belongsTo(Profile::class, 'following_id');
    }
}
