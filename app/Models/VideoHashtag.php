<?php

namespace App\Models;

use App\Observers\VideoHashtagObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([VideoHashtagObserver::class])]
class VideoHashtag extends Model
{
    //
}
