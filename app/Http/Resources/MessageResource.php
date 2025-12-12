<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'message_text' => $this->message_text,
            'created_at' => $this->created_at,

            // ✅ Include reply context
            'reply_to_message_id' => $this->reply_to_message_id,
            'reply_to_sender_name' => $this->reply_to_sender_name,
            'reply_to_message_text' => $this->reply_to_message_text,

            'attachment_url' => $this->attachment_url
                ? asset('storage/' . $this->attachment_url)
                : null,
        ];
    }
}