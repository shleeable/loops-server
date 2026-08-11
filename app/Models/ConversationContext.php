<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationContext extends Model
{
    protected $fillable = ['conversation_id', 'context_uri'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
