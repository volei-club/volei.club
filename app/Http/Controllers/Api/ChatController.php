<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.sender'])
            ->get();

        // Calculate unread status server-side for consistency
        $conversations->each(function ($conv) use ($user) {
            $myPivot = $conv->users->first(fn($u) => (string)$u->id === (string)$user->id)?->pivot;
            $conv->is_unread = false;

            if ($conv->lastMessage && (string)$conv->lastMessage->sender_id !== (string)$user->id) {
                $lastRead = $myPivot?->last_read_at ? Carbon::parse($myPivot->last_read_at) : null;
                // If never read or last message is newer than last read (with safe comparison)
                if (!$lastRead || Carbon::parse($conv->lastMessage->created_at)->gt($lastRead)) {
                    $conv->is_unread = true;
                }
            }
        });

        $conversations = $conversations->sortByDesc(function ($populated) {
            return $populated->lastMessage ? $populated->lastMessage->created_at : $populated->created_at;
        })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $conversations
        ]);
    }

    public function show(Conversation $conversation, Request $request)
    {
        if (!$conversation->users->contains($request->user()->id)) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required_without:conversation_id|exists:users,id',
            'conversation_id' => 'required_without:recipient_id|exists:conversations,id',
            'content' => 'required|string',
        ]);

        $user = $request->user();
        $conversationId = $request->conversation_id;

        if (!$conversationId) {
            // Check if direct conversation already exists
            $recipientId = $request->recipient_id;

            $existing = Conversation::where('type', 'direct')
                ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
                ->whereHas('users', function ($q) use ($recipientId) {
                $q->where('users.id', $recipientId);
            })
                ->first();

            if ($existing) {
                $conversationId = $existing->id;
            }
            else {
                // Create new direct conversation
                $conversation = Conversation::create(['type' => 'direct']);
                $conversation->users()->attach([$user->id, $recipientId]);
                $conversationId = $conversation->id;
            }
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'content' => $request->content,
            'type' => 'text'
        ]);

        // Update last_read_at for sender
        DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data' => $message->load('sender')
        ]);
    }

    public function getContacts(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $clubId = $user->club_id;

        $query = User::where('id', '!=', $user->id)->where('is_active', true);

        if ($role === 'administrator') {
        // Admin sees everyone
        }
        elseif ($role === 'manager') {
            // Manager sees club members + administrators
            $query->where(function ($q) use ($clubId) {
                $q->where('club_id', $clubId)
                    ->orWhere('role', 'administrator');
            });
        }
        elseif ($role === 'antrenor') {
            // Coach sees: Manager, Athletes in their squads, Parents of those athletes
            $squadIds = $user->squads->pluck('id');
            // Also include squads from teams they are assigned to
            $teamIds = $user->teams->pluck('id');
            $teamSquadIds = \App\Models\Squad::whereIn('team_id', $teamIds)->pluck('id');
            $allAssociatedSquadIds = $squadIds->concat($teamSquadIds)->unique();

            $query->where(function ($q) use ($clubId, $allAssociatedSquadIds) {
                $q->where(function ($sq) use ($clubId) {
                        $sq->where('club_id', $clubId)->where('role', 'manager');
                    }
                    )
                        ->orWhereHas('squads', function ($sq) use ($allAssociatedSquadIds) {
                    $sq->whereIn('squads.id', $allAssociatedSquadIds);
                }
                )
                    ->orWhereHas('children.squads', function ($sq) use ($allAssociatedSquadIds) {
                    $sq->whereIn('squads.id', $allAssociatedSquadIds);
                }
                );
            });
        }
        elseif ($role === 'sportiv') {
            // Athlete sees: Coaches of their squads, Managers of their club, Teammates
            $squadIds = $user->squads->pluck('id');

            $query->where(function ($q) use ($clubId, $squadIds) {
                $q->where(function ($sq) use ($clubId) {
                        $sq->where('club_id', $clubId)->where('role', 'manager');
                    }
                    )
                ->orWhere(function ($sq) use ($squadIds) {
                    $sq->where('role', 'antrenor')
                        ->where(function ($qq) use ($squadIds) {
                        $qq->whereHas('squads', fn($q) => $q->whereIn('squads.id', $squadIds))
                            ->orWhereHas('teams', function ($q) use ($squadIds) {
                            $teamIds = \App\Models\Squad::whereIn('id', $squadIds)->pluck('team_id');
                            $q->whereIn('teams.id', $teamIds);
                        });
                    });
                })
                ->orWhere(function ($sq) use ($squadIds) {
                    // Teammates: other athletes in the same squads
                    $sq->where('role', 'sportiv')
                       ->whereHas('squads', fn($q) => $q->whereIn('squads.id', $squadIds));
                });
            });
        }
        elseif ($role === 'parinte') {
            // Parent sees: Coaches of their children's squads, Managers of their club
            $children = $user->children()->with('squads')->get();
            $childrenSquadIds = $children->pluck('squads')->flatten()->pluck('id')->unique();
            $childrenTeamIds = \App\Models\Squad::whereIn('id', $childrenSquadIds)->pluck('team_id')->unique();

            $query->where(function ($q) use ($clubId, $childrenSquadIds, $childrenTeamIds) {
                $q->where(function ($sq) use ($clubId) {
                        $sq->where('club_id', $clubId)->where('role', 'manager');
                    }
                    )
                        ->orWhere(function ($sq) use ($childrenSquadIds, $childrenTeamIds) {
                    $sq->where('role', 'antrenor')
                        ->where(function ($qq) use ($childrenSquadIds, $childrenTeamIds) {
                        $qq->whereHas('squads', fn($q) => $q->whereIn('squads.id', $childrenSquadIds))
                            ->orWhereHas('teams', fn($q) => $q->whereIn('teams.id', $childrenTeamIds));
                    }
                    );
                }
                );
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('name')->get(['id', 'name', 'role', 'photo', 'club_id'])
        ]);
    }

    public function markAsRead(Conversation $conversation, Request $request)
    {
        DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $request->user()->id)
            ->update(['last_read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        // Using a more explicit query to avoid relationship issues
        $count = DB::table('conversations')
            ->join('conversation_user', 'conversations.id', '=', 'conversation_user.conversation_id')
            ->join('messages', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversation_user.user_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('conversation_user.last_read_at')
                  ->orWhereColumn('messages.created_at', '>', 'conversation_user.last_read_at');
            })
            ->distinct('conversations.id')
            ->count('conversations.id');

        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }
}