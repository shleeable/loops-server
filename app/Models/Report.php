<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public $guarded = [];

    public function reportEntityType()
    {
        if($this->reported_profile_id) {
            return 'profile';
        }

        if($this->reported_video_id) {
            return 'video';
        }

        if($this->reported_comment_id) {
            return 'comment';
        }

        return 'Undefined Type';
    }

    public function video()
    {
        return $this->hasOne(Video::class, 'id', 'reported_video_id');
    }

    public function adminUrl()
    {
        return url('/admin/reports/show/' . $this->id);
    }
}
