<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $userId = auth('web')->user()->id;
        $chats = Chat::with(['fromUser', 'toUser', 'lastMessage', 'unreadMessages'])
            ->where(function ($q) use ($userId) {
                $q->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            })
            ->latest('updated_at')
            ->get();
        return view('website.my_account.chat.chats', compact('chats'));
    }

    public function show($uuid)
    {
        $chat = Chat::where('uuid', $uuid)
            ->with('messages.sender')
            ->firstOrFail();
        $chat->messages()
            ->where('sender_id','!=', auth('web')->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        return response()->json($chat->messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,uuid',
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::where('uuid' , $request->chat_id)->select('id')->first();

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => auth('web')->id(),
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('user'),
        ]);
    }

    public function openChat($uuid)
    {
        $authUser = auth('web')->user();

        $receiver = User::where('uuid', $uuid)->select('id')->firstOrFail();

        if ($authUser->id == $receiver->id) {
            return redirect()->back()->withErrors('لا يمكنك فتح محادثة مع نفسك');
        }

        $chat = Chat::where(function ($q) use ($authUser, $receiver) {
            $q->where('from_user_id', $authUser->id)
                ->where('to_user_id', $receiver->id);
        })->orWhere(function ($q) use ($authUser, $receiver) {
            $q->where('from_user_id', $receiver->id)
                ->where('to_user_id', $authUser->id);
        })->first();

        if (!$chat) {
            $chat = Chat::create([
                'from_user_id' => $authUser->id,
                'to_user_id' => $receiver->id,
                'active' => 1,
            ]);
        }

        $receiver = User::findOrFail($receiver->id);
        return view('website.my_account.chat.single_chat', compact('chat', 'receiver'));
    }

}
