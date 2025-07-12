<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReplyLike extends Model
{
    use HasFactory;

    public $fillable = ['profile_id', 'comment_id'];

    public function comment()
    {
        return $this->belongsTo(CommentReply::class);
    }
}
