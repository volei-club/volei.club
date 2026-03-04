<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * List conversations for the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = $this->chatService->listConversations($user);

        return response()->json([
            'status' => 'success',
            'data' => $conversations
        ]);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $conversation = $this->chatService->getConversationById($id, ['users', 'messages.sender']);

        // Authorization: Check if user is part of the conversation
        if (!$conversation->users->contains($user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mark as read when opening conversation
        $this->chatService->markAsRead($conversation, $user->id);

        $otherUser = $conversation->users->first(fn($u) => $u->id !== $user->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'conversation' => $conversation,
                'other_user' => $otherUser,
                'messages' => $conversation->messages->sortBy('created_at')->values()
            ]
        ]);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $sender = $request->user();
        $receiverId = $request->receiver_id;

        $conversation = $this->chatService->getConversation($sender->id, $receiverId);
        $message = $this->chatService->sendMessage($conversation, $sender->id, $request->content);

        // After sending, we might want to mark it as read for the sender (though it's their own message)
        $this->chatService->markAsRead($conversation, $sender->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mesaj trimis.',
            'data' => $message->load('sender')
        ]);
    }

    /**
     * Get total unread messages count.
     */
    public function unreadCount(Request $request)
    {
        $count = $this->chatService->getUnreadCount($request->user());

        return response()->json([
            'status' => 'success',
            'unread_count' => $count
        ]);
    }

    /**
     * Get list of potential contacts.
     */
    public function getContacts(Request $request)
    {
        $contacts = $this->chatService->getAvailableContacts($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    /**
     * Mark a conversation as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $conversation = $this->chatService->getConversationById($id);
        $this->chatService->markAsRead($conversation, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Conversație marcată ca citită.'
        ]);
    }
}