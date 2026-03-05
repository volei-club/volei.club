<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Club;
use App\Models\Location;
use App\Models\Squad;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $manager;
    protected $coach;
    protected $athlete;
    protected $squad;
    protected $training;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->manager = User::factory()->create(['role' => 'manager', 'club_id' => $this->club->id]);
        $this->coach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);
        $this->athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $location = Location::create([
            'club_id' => $this->club->id,
            'name' => 'Test Hall',
            'address' => 'Test St 1',
        ]);
        $team = Team::create(['club_id' => $this->club->id, 'name' => 'Test Team']);

        $this->squad = Squad::create([
            'team_id' => $team->id,
            'name' => 'Test Squad',
            'created_by' => $admin->id,
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

    // ── Index ───────────────────────────────────────────────────────────────

    public function test_manager_can_list_attendance_for_training(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/attendances?' . http_build_query([
            'training_id' => $this->training->id,
            'date' => '2026-03-10',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_coach_can_list_attendance_for_own_training(): void
    {
        $response = $this->actingAs($this->coach, 'sanctum')
            ->getJson('/api/attendances?' . http_build_query([
            'training_id' => $this->training->id,
            'date' => '2026-03-10',
        ]));

        $response->assertStatus(200);
    }

    public function test_other_coach_cannot_list_attendance_for_training(): void
    {
        $otherCoach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);

        $response = $this->actingAs($otherCoach, 'sanctum')
            ->getJson('/api/attendances?' . http_build_query([
            'training_id' => $this->training->id,
            'date' => '2026-03-10',
        ]));

        $response->assertStatus(403);
    }

    // ── Store ───────────────────────────────────────────────────────────────

    public function test_coach_can_mark_attendance(): void
    {
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson('/api/attendances', [
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'prezent',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendances', [
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'status' => 'prezent',
        ]);
    }

    public function test_manager_can_mark_attendance(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/attendances', [
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'absent',
        ]);

        $response->assertStatus(201);
    }

    public function test_athlete_cannot_mark_attendance(): void
    {
        $this->actingAs($this->athlete, 'sanctum')
            ->postJson('/api/attendances', [
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'prezent',
        ])
            ->assertStatus(403);
    }

    public function test_marking_attendance_creates_or_updates_record(): void
    {
        // First call – creates
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson('/api/attendances', [
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'prezent',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['status' => 'prezent']);
    }

    // ── Destroy ─────────────────────────────────────────────────────────────

    public function test_coach_can_delete_attendance_record(): void
    {
        $attendance = Attendance::create([
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'prezent',
            'marked_by' => $this->coach->id,
        ]);

        $this->actingAs($this->coach, 'sanctum')
            ->deleteJson("/api/attendances/{$attendance->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }

    public function test_athlete_cannot_delete_attendance_record(): void
    {
        $attendance = Attendance::create([
            'training_id' => $this->training->id,
            'user_id' => $this->athlete->id,
            'date' => '2026-03-10',
            'status' => 'prezent',
            'marked_by' => $this->coach->id,
        ]);

        $this->actingAs($this->athlete, 'sanctum')
            ->deleteJson("/api/attendances/{$attendance->id}")
            ->assertStatus(403);
    }
}
