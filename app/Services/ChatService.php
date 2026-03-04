<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatService
{
    /**
     * List conversations for the authenticated user.
     */
    public function listConversations(User $user)
    {
        return Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->with(['users', 'lastMessage'])
        ->get()
        ->map(function ($conversation) use ($user) {
            $otherUser = $conversation->users->first(fn($u) => $u->id !== $user->id);
            return [
                'id' => $conversation->id,
                'other_user' => $otherUser,
                'users' => $conversation->users,
                'last_message' => $conversation->lastMessage,
                'unread_count' => $this->getConversationUnreadCount($conversation, $user),
                'updated_at' => $conversation->updated_at,
            ];
        })
        ->sortByDesc('updated_at')
        ->values();
    }

    /**
     * Get or create a direct conversation between two users.
     */
    public function getConversation(string $userOneId, string $userTwoId)
    {
        // Find a 'direct' conversation where BOTH users are members
        $conversation = Conversation::where('type', 'direct')
            ->whereHas('users', function($q) use ($userOneId) {
                $q->where('users.id', $userOneId);
            })
            ->whereHas('users', function($q) use ($userTwoId) {
                $q->where('users.id', $userTwoId);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'direct',
            ]);
            $conversation->users()->attach([$userOneId, $userTwoId]);
        }

        return $conversation;
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, string $senderId, string $content)
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'content' => $content,
        ]);

        // Touch the conversation to update its updated_at timestamp
        $conversation->touch();

        return $message;
    }

    /**
     * Mark messages in a conversation as read for a specific user.
     */
    public function markAsRead(Conversation $conversation, string $userId)
    {
        $conversation->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * Get unread messages count for a user across all conversations.
     */
    public function getUnreadCount(User $user)
    {
        $conversations = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();

        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $totalUnread += $this->getConversationUnreadCount($conv, $user);
        }

        return $totalUnread;
    }

    /**
     * Internal helper to count unread messages in a specific conversation for a user.
     */
    protected function getConversationUnreadCount(Conversation $conversation, User $user)
    {
        $pivot = $conversation->users()->where('users.id', $user->id)->first()?->pivot;
        $lastReadAt = $pivot ? $pivot->last_read_at : null;

        $query = $conversation->messages()->where('sender_id', '!=', $user->id);

        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }

        return $query->count();
    }

    /**
     * Get potential chat contacts based on user role and club.
     */
    public function getAvailableContacts(User $user)
    {
        $query = User::where('id', '!=', $user->id);

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role === 'antrenor') {
            // Can see manager and athletes in their squads
            $squadUserIds = DB::table('squad_user')
                ->whereIn('squad_id', $user->squads()->pluck('squads.id'))
                ->pluck('user_id');

            $query->where(function ($q) use ($user, $squadUserIds) {
                $q->where('club_id', $user->club_id)
                    ->whereIn('role', ['manager', 'administrator'])
                    ->orWhereIn('id', $squadUserIds);
            });
        }
        elseif ($user->role === 'sportiv') {
            // Can see manager, their coaches, and teammates
            $squadIds = $user->squads()->pluck('squads.id');
            $teammateIds = DB::table('squad_user')
                ->whereIn('squad_id', $squadIds)
                ->pluck('user_id');

            $query->where(function ($q) use ($user, $teammateIds) {
                $q->where('club_id', $user->club_id)
                    ->whereIn('role', ['manager', 'administrator', 'antrenor'])
                    ->orWhereIn('id', $teammateIds);
            });
        }
        elseif ($user->role === 'parinte') {
            // Can see manager and coaches of their children
            $childSquadIds = $user->children()->with('squads')->get()->pluck('squads.*.id')->flatten()->unique();
            $coachIds = User::where('role', 'antrenor')
                ->whereHas('squads', fn($q) => $q->whereIn('squads.id', $childSquadIds))
                ->pluck('id');

            $query->where(function ($q) use ($user, $coachIds) {
                $q->where('club_id', $user->club_id)
                    ->whereIn('role', ['manager', 'administrator'])
                    ->orWhereIn('id', $coachIds);
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get a conversation by ID, with optional eager-loaded relations.
     */
    public function getConversationById(string $id, array $relations = []): Conversation
    {
        $query = Conversation::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }
}
