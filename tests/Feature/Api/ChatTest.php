<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_conversation_and_send_messages()
    {
        $this->withoutExceptionHandling();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 1. Send message (creates conversation)
        $this->actingAs($user1, 'sanctum');

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $user2->id,
            'content' => 'Test message'
        ]);

        if ($response->status() !== 200) {
            fwrite(STDERR, $response->content() . "\n");
        }

        $response->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $user1->id,
            'content' => 'Test message'
        ]);

        $conversationId = $response->json('data.conversation_id');
        if (!$conversationId) {
            fwrite(STDERR, "Conversation ID is null!\n");
            fwrite(STDERR, print_r($response->json(), true));
        }

        // 2. Check unread count for receiver
        $this->actingAs($user2, 'sanctum');
        $unreadResponse = $this->getJson('/api/chat/unread-count');
        $unreadResponse->assertStatus(200);
        $this->assertEquals(1, $unreadResponse->json('unread_count'));

        // 3. Mark as read
        $this->postJson("/api/chat/conversations/{$conversationId}/read")->assertStatus(200);

        // 4. Check unread count again
        $unreadResponse2 = $this->getJson('/api/chat/unread-count');
        $this->assertEquals(0, $unreadResponse2->json('unread_count'));
    }

    public function test_can_list_conversations()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create(['type' => 'direct']);
        $conversation->users()->attach([$user1->id, $user2->id]);

        $this->actingAs($user1, 'sanctum');
        $response = $this->getJson('/api/chat/conversations');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
