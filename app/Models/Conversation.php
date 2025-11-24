<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'created_by'];

    /**
     * Participants of this conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'conversation_participants',
            'conversation_id',
            'user_id'
        );
    }

    /**
     * All messages belonging to this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class)
                    ->orderBy('created_at', 'asc')
                    ->with('sender:id,name,avatar');
    }

    /**
     * The most recent message in this conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
