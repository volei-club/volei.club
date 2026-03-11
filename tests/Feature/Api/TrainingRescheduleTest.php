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

class TrainingRescheduleTest extends TestCase
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
            'day_of_week' => 'luni', // Monday
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);
    }

    public function test_rescheduling_training_same_day_sends_notifications(): void
    {
        $originalMonday = '2026-03-09';
        
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson("/api/trainings/{$this->training->id}/reschedule-instance", [
                'original_date' => $originalMonday,
                'new_date' => $originalMonday,
                'new_start_time' => '14:00',
                'new_end_time' => '16:00',
                'reason' => 'Coach busy in morning',
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('training_reschedules', [
            'training_id' => $this->training->id,
            'original_date' => $originalMonday,
            'new_date' => $originalMonday,
            'new_start_time' => '14:00',
        ]);

        // Check notifications
        $this->assertConversationMessageContains($this->athlete, 'reprogramată');
        $this->assertConversationMessageContains($this->parent, 'reprogramată');
    }

    public function test_rescheduling_to_different_day_updates_calendar(): void
    {
        $originalMonday = '2026-03-09';
        $newTuesday = '2026-03-10';

        $this->actingAs($this->coach, 'sanctum')
            ->postJson("/api/trainings/{$this->training->id}/reschedule-instance", [
                'original_date' => $originalMonday,
                'new_date' => $newTuesday,
                'new_start_time' => '10:00',
                'new_end_time' => '12:00',
                'reason' => 'Hall unavailable on Monday',
            ]);

        // Check calendar
        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson('/api/my-calendar?start_date=2026-03-09&weeks=1');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Original Monday should NOT have a training session
        $mondaySession = collect($data)->first(fn($s) => $s['date'] === $originalMonday && $s['type'] === 'training');
        $this->assertNull($mondaySession, 'Monday session should be gone from calendar');

        // New Tuesday SHOULD have a training session
        $tuesdaySession = collect($data)->first(fn($s) => $s['date'] === $newTuesday && $s['type'] === 'training');
        $this->assertNotNull($tuesdaySession, 'Tuesday session should appear in calendar');
        $this->assertEquals($this->training->id, $tuesdaySession['training_id']);
    }

    public function test_unreschedule_restores_original_time(): void
    {
        $originalMonday = '2026-03-09';

        // 1. Reschedule
        $this->actingAs($this->coach, 'sanctum')
            ->postJson("/api/trainings/{$this->training->id}/reschedule-instance", [
                'original_date' => $originalMonday,
                'new_date' => $originalMonday,
                'new_start_time' => '18:00',
                'new_end_time' => '20:00',
            ]);

        // 2. Unreschedule
        $response = $this->actingAs($this->coach, 'sanctum')
            ->deleteJson("/api/trainings/{$this->training->id}/reschedule-instance", [
                'original_date' => $originalMonday,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('training_reschedules', ['original_date' => $originalMonday]);

        // 3. Check calendar
        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson('/api/my-calendar?start_date=2026-03-09&weeks=1');
        
        $session = collect($response->json('data'))->first(fn($s) => $s['date'] === $originalMonday);
        $this->assertEquals('10:00', $session['start_time'], 'Should revert to original start time');
    }

    protected function assertConversationMessageContains($user, $text)
    {
        $conversation = Conversation::whereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->whereHas('users', fn($q) => $q->where('users.id', $this->coach->id))
            ->first();

        $this->assertNotNull($conversation, "Conversation with user {$user->name} should exist");
        
        $message = Message::where('conversation_id', $conversation->id)->latest()->first();
        $this->assertStringContainsStringIgnoringCase($text, $message->content);
    }
}
