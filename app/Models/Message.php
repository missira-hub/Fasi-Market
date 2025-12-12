<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

   protected $fillable = [
    'conversation_id',
    'sender_id',
    'message_text',
    'attachment_url',
    'is_read',
    // ✅ Add reply fields
    'reply_to_message_id',
    'reply_to_sender_name',
    'reply_to_message_text',
];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    
}
