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

        if(!$conversation->participants()->where('user_id', auth()->id())->exists()){
            return response()->json(['message'=>'Not a participant'],403);
        }

        $messages = Message::with('sender:id,name,avatar')
            ->where('conversation_id',$conversationId)
            ->orderBy('created_at','asc')
            ->get();

        return response()->json($messages);
    }

    // --- Send a message
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message_text' => 'required|string'
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        if(!$conversation->participants()->where('user_id', auth()->id())->exists()){
            return response()->json(['message'=>'Not a participant'],403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'message_text' => $request->message_text,
            'is_read' => false,
        ]);

        $message->load('sender');

        return response()->json($message,201);
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
}