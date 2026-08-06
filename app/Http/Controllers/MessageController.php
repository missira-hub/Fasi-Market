<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // --- List messages
    public function index($conversationId)
{
    $conversation = Conversation::findOrFail($conversationId);

    if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
        return response()->json(['message' => 'Not a participant'], 403);
    }

    $messages = Message::with('sender:id,name,avatar')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at', 'asc')
        ->get();

    // ✅ Format messages with full attachment URLs
    return response()->json($messages->map(function ($msg) {
        return [
            'id' => $msg->id,
            'conversation_id' => $msg->conversation_id,
            'sender_id' => $msg->sender_id,
            'sender' => $msg->sender,
            'message_text' => $msg->message_text,
            'attachment_url' => $msg->attachment_url
                ? url('storage/' . $msg->attachment_url)
                : null,
            'is_read' => $msg->is_read,
            'created_at' => $msg->created_at,
            'updated_at' => $msg->updated_at,

            // ✅ Include reply context (if you're saving reply_to_message_id)
            'reply_to_message_id' => $msg->reply_to_message_id,
            'reply_to_sender_name' => $msg->reply_to_sender_name,
            'reply_to_message_text' => $msg->reply_to_message_text,
        ];
    }));
}
public function store(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,id',
        'message_text' => 'required|string',
        'reply_to_message_id' => 'nullable|exists:messages,id',
        'reply_to_sender_name' => 'nullable|string|max:255',
        'reply_to_message_text' => 'nullable|string',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
    ]);

    $message = new Message();
    $message->conversation_id = $request->conversation_id;
    $message->sender_id = auth()->id();
    $message->message_text = $request->message_text;

    // Save reply context if provided
    if ($request->filled('reply_to_message_id')) {
        $message->reply_to_message_id = $request->reply_to_message_id;
        $message->reply_to_sender_name = $request->reply_to_sender_name;
        $message->reply_to_message_text = $request->reply_to_message_text;
    }

    // Handle attachment if needed
    if ($request->hasFile('attachment')) {
        $message->attachment_url = $request->file('attachment')->store('messages', 'public');
    }

// In MessageController@store
$message->save();

// ✅ Load sender with name and avatar
$message->load('sender:id,name,avatar_url');


    // Return full message with reply data
    return response()->json($message, 201);
}

    // --- Delete a message
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        
        // Check if user is a participant in the conversation
        $conversation = Conversation::findOrFail($message->conversation_id);
        if(!$conversation->participants()->where('user_id', auth()->id())->exists()){
            return response()->json(['message'=>'Not a participant'],403);
        }
        
        // Check if the authenticated user is the sender of the message
        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message'=>'You can only delete your own messages'],403);
        }
        
        $message->delete();
        
        return response()->json(['message'=>'Message deleted successfully']);
    }
    // MessageController.php

public function markAsRead($conversationId)
{
    // Mark all messages in this conversation (that belong to the current user) as read
    Message::where('conversation_id', $conversationId)
        ->where('sender_id', '!=', auth()->id()) // Only messages sent by others
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json(['message' => 'Messages marked as read']);
}
// App\Models\Message.php
public function replyToMessage()
{
    return $this->belongsTo(Message::class, 'reply_to_message_id');
}
}