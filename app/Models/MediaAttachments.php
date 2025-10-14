<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;

class MediaAttachments extends Model
{
    use HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
