<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\PerformanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $coach;
    protected $athlete;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->manager = User::factory()->create(['role' => 'manager', 'club_id' => $this->club->id]);
        $this->coach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);
        $this->athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);
    }

    private function logPayload(array $overrides = []): array
    {
        return array_merge([
            'user_id' => $this->athlete->id,
            'log_date' => '2026-03-01',
            'vertical_jump' => 65,
            'serve_speed' => 80,
            'reception_rating' => 4,
        ], $overrides);
    }

    // ── Index ───────────────────────────────────────────────────────────────

    public function test_coach_can_view_athlete_performance(): void
    {
        PerformanceLog::create([
            'user_id' => $this->athlete->id,
            'coach_id' => $this->coach->id,
            'log_date' => '2026-03-01',
        ]);

        $response = $this->actingAs($this->coach, 'sanctum')
            ->getJson("/api/performance/{$this->athlete->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');
    }

    public function test_athlete_can_view_own_performance(): void
    {
        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson("/api/performance/{$this->athlete->id}");

        $response->assertStatus(200);
    }

    public function test_athlete_cannot_view_other_athlete_performance(): void
    {
        $otherAthlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $this->actingAs($this->athlete, 'sanctum')
            ->getJson("/api/performance/{$otherAthlete->id}")
            ->assertStatus(403);
    }

    public function test_parent_can_view_child_performance(): void
    {
        $parent = User::factory()->create(['role' => 'parinte', 'club_id' => $this->club->id]);
        $parent->children()->attach($this->athlete->id);

        $response = $this->actingAs($parent, 'sanctum')
            ->getJson("/api/performance/{$this->athlete->id}");

        $response->assertStatus(200);
    }

    public function test_parent_cannot_view_non_child_performance(): void
    {
        $parent = User::factory()->create(['role' => 'parinte', 'club_id' => $this->club->id]);
        $otherAthlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $this->actingAs($parent, 'sanctum')
            ->getJson("/api/performance/{$otherAthlete->id}")
            ->assertStatus(403);
    }

    // ── Store ───────────────────────────────────────────────────────────────

    public function test_coach_can_log_performance_entry(): void
    {
        $response = $this->actingAs($this->coach, 'sanctum')
            ->postJson('/api/performance', $this->logPayload());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('performance_logs', [
            'user_id' => $this->athlete->id,
            'coach_id' => $this->coach->id,
        ]);
    }

    public function test_manager_can_log_performance_for_own_club_athlete(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/performance', $this->logPayload());

        $response->assertStatus(201);
    }

    public function test_manager_cannot_log_performance_for_other_club_athlete(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherAthlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $otherClub->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/performance', $this->logPayload(['user_id' => $otherAthlete->id]))
            ->assertStatus(403);
    }

    public function test_athlete_cannot_log_performance(): void
    {
        $this->actingAs($this->athlete, 'sanctum')
            ->postJson('/api/performance', $this->logPayload())
            ->assertStatus(403);
    }

    // ── Destroy ─────────────────────────────────────────────────────────────

    public function test_coach_can_delete_own_performance_entry(): void
    {
        $log = PerformanceLog::create([
            'user_id' => $this->athlete->id,
            'coach_id' => $this->coach->id,
            'log_date' => '2026-03-01',
        ]);

        $this->actingAs($this->coach, 'sanctum')
            ->deleteJson("/api/performance/{$log->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('performance_logs', ['id' => $log->id]);
    }

    public function test_coach_cannot_delete_other_coach_entry(): void
    {
        $otherCoach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);
        $log = PerformanceLog::create([
            'user_id' => $this->athlete->id,
            'coach_id' => $otherCoach->id,
            'log_date' => '2026-03-01',
        ]);

        $this->actingAs($this->coach, 'sanctum')
            ->deleteJson("/api/performance/{$log->id}")
            ->assertStatus(403);
    }

    public function test_administrator_can_delete_any_performance_entry(): void
    {
        $admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $log = PerformanceLog::create([
            'user_id' => $this->athlete->id,
            'coach_id' => $this->coach->id,
            'log_date' => '2026-03-01',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/performance/{$log->id}")
            ->assertStatus(200);
    }
}
