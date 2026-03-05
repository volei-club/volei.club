<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Squad;
use App\Models\Team;
use App\Models\Training;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $admin;
    protected $manager;
    protected $athlete;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $this->manager = User::factory()->create(['role' => 'manager', 'club_id' => $this->club->id]);
        $this->athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);
    }

    public function test_unauthenticated_user_cannot_access_dashboard_stats(): void
    {
        $this->getJson('/api/dashboard-stats')->assertStatus(401);
    }

    public function test_administrator_can_access_dashboard_stats(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/dashboard-stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_manager_can_access_dashboard_stats(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/dashboard-stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_manager_sees_only_own_club_counts(): void
    {
        // Add a user to our club
        User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        // Add a user to another club
        $otherClub = Club::create(['name' => 'Other Club']);
        User::factory()->create(['role' => 'sportiv', 'club_id' => $otherClub->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/dashboard-stats');

        $response->assertStatus(200);
        $sportivi = $response->json('data.kpi.sportivi');
        // Manager's club has 2 sportivi (setUp athlete + the one we just created)
        $this->assertEquals(2, $sportivi);
    }

    public function test_athlete_can_access_dashboard_stats(): void
    {
        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson('/api/dashboard-stats');

        $response->assertStatus(200);
    }
}
