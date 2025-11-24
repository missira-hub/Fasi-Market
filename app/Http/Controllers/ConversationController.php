<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // Use sanctum for API auth
    }

    // --- List all conversations for logged-in user
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with([
            'participants:id,name,avatar',          // load avatar column
            'latestMessage.sender:id,name,avatar', // load sender avatar
        ])
        ->whereHas('participants', function($q) use ($userId){
            $q->where('user_id', $userId);
        })
        ->orderByDesc('updated_at')
        ->get()
        ->map(function($conv) use ($userId){
            $other = $conv->participants->where('id','!=',$userId)->first();
            $conv->chat_name = $other ? $other->name : 'Unknown User';
            $conv->chat_avatar = $other ? $other->avatar_url : '/default-avatar.png';

            $conv->unread_count = $conv->messages()
                ->where('is_read', false)
                ->where('sender_id','!=',$userId)
                ->count();

            return $conv;
        });

        return response()->json($conversations->values());
    }

    // --- Start or get a conversation
    public function start(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $authId = $request->user()->id;
        $otherUserId = $request->user_id;

        $conversation = Conversation::whereHas('participants', function($q) use($authId){
            $q->where('user_id',$authId);
        })->whereHas('participants', function($q) use($otherUserId){
            $q->where('user_id',$otherUserId);
        })->first();

        if(!$conversation){
            $conversation = Conversation::create([
                'title' => "Chat between $authId and $otherUserId",
                'created_by' => $authId
            ]);

            $conversation->participants()->attach([$authId,$otherUserId]);
        }

        return response()->json([
            'conversation' => $conversation->load('participants')
        ]);
    }

    // --- View single conversation with messages
    public function show($id)
    {
        $conversation = Conversation::with([
            'participants:id,name,avatar',
            'messages.sender:id,name,avatar'
        ])->findOrFail($id);

        if(!$conversation->participants()->where('user_id', auth()->id())->exists()){
            return response()->json(['message'=>'Not a participant'], 403);
        }

        return response()->json($conversation);
    }
    // ConversationController.php
   // Delete conversation
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        
        // Check if user is a participant
        if(!$conversation->participants()->where('user_id', auth()->id())->exists()){
            return response()->json(['message'=>'Not a participant'],403);
        }
        
        // Delete the conversation and all its messages
        $conversation->delete();
        
        return response()->json(['message'=>'Conversation deleted successfully']);
    }
}
