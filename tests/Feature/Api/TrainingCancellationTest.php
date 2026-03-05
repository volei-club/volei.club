<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Location;
use App\Models\Squad;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $coach;
    protected $athlete;
    protected $parent;
    protected $squad;
    protected $training;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->coach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);
        $this->athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);
        $this->parent = User::factory()->create(['role' => 'parinte', 'club_id' => $this->club->id]);

        // Link parent and athlete
        DB::table('parent_student')->insert([
            'parent_id' => $this->parent->id,
            'student_id' => $this->athlete->id,
        ]);

        $location = Location::create([
            'club_id' => $this->club->id,
            'name' => 'Test Hall',
            'address' => 'Test St 1',
        ]);

        $team = Team::create(['club_id' => $this->club->id, 'name' => 'Test Team']);

        $this->squad = Squad::create([
            'team_id' => $team->id,
            'name' => 'Test Squad',
            'created_by' => User::factory()->create(['role' => 'administrator'])->id,
        ]);
        $this->squad->users()->attach($this->athlete->id);

        $this->training = Training::create([
            'club_id' => $this->club->id,
            'location_id' => $location->id,
            'team_id' => $team->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'luni',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);
    }

    public function test_cancelling_training_sends_notifications(): void
    {
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson("/api/trainings/{$this->training->id}/cancel-instance", [
            'date' => '2026-03-09',
            'reason' => 'Emergency maintenance',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('reason', 'Emergency maintenance');

        $this->assertDatabaseHas('training_cancellations', [
            'training_id' => $this->training->id,
            'date' => '2026-03-09',
            'reason' => 'Emergency maintenance',
        ]);

        // Check if messages were sent to athlete
        $atheteConversation = Conversation::whereHas('users', fn($q) => $q->where('users.id', $this->athlete->id))
            ->whereHas('users', fn($q) => $q->where('users.id', $this->coach->id))
            ->first();

        $this->assertNotNull($atheteConversation, 'Conversation with athlete should exist');
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $atheteConversation->id,
            'sender_id' => $this->coach->id,
        ]);

        $athleteMessage = Message::where('conversation_id', $atheteConversation->id)->first();
        $this->assertStringContainsString('ANULATĂ', str_replace('anulată', 'ANULATĂ', strtolower($athleteMessage->content)));
        $this->assertStringContainsString('Emergency maintenance', $athleteMessage->content);

        // Check if messages were sent to parent
        $parentConversation = Conversation::whereHas('users', fn($q) => $q->where('users.id', $this->parent->id))
            ->whereHas('users', fn($q) => $q->where('users.id', $this->coach->id))
            ->first();

        $this->assertNotNull($parentConversation, 'Conversation with parent should exist');
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $parentConversation->id,
            'sender_id' => $this->coach->id,
        ]);

        $parentMessage = Message::where('conversation_id', $parentConversation->id)->first();
        $this->assertStringContainsString('ANULATĂ', str_replace('anulată', 'ANULATĂ', strtolower($parentMessage->content)));
        $this->assertStringContainsString('Emergency maintenance', $parentMessage->content);
    }

    public function test_cancelling_with_whitespace_reason_uses_fallback(): void
    {
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson("/api/trainings/{$this->training->id}/cancel-instance", [
            'date' => '2026-03-16',
            'reason' => '   ', // whitespace only
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('reason', __('trainings.notifications.unspecified_reason'));

        $atheteConversation = Conversation::whereHas('users', fn($q) => $q->where('users.id', $this->athlete->id))
            ->whereHas('users', fn($q) => $q->where('users.id', $this->coach->id))
            ->first();

        $message = Message::where('conversation_id', $atheteConversation->id)->latest()->first();
        $this->assertStringContainsString(__('trainings.notifications.unspecified_reason'), $message->content);
    }
}
