<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Squad;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SquadApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $club;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $this->manager = User::factory()->create(['role' => 'manager', 'club_id' => $this->club->id]);
        $this->team = Team::create(['club_id' => $this->club->id, 'name' => 'Test Team']);
    }

    // ── List ────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_squads(): void
    {
        $this->getJson('/api/squads')->assertStatus(401);
    }

    public function test_administrator_can_list_all_squads(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);

        Squad::create(['team_id' => $this->team->id, 'name' => 'Squad A', 'created_by' => $this->admin->id]);
        Squad::create(['team_id' => $otherTeam->id, 'name' => 'Squad B', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/squads');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_manager_can_only_list_squads_from_own_club(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);

        Squad::create(['team_id' => $this->team->id, 'name' => 'My Squad', 'created_by' => $this->admin->id]);
        Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->manager, 'sanctum')->getJson('/api/squads');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // ── Store ───────────────────────────────────────────────────────────────

    public function test_manager_can_create_squad_in_own_club_team(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/squads', [
            'name' => 'Senioare',
            'team_id' => $this->team->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Senioare');

        $this->assertDatabaseHas('squads', ['name' => 'Senioare', 'team_id' => $this->team->id]);
    }

    public function test_manager_cannot_create_squad_in_other_club_team(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/squads', [
            'name' => 'Junioare',
            'team_id' => $otherTeam->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_athlete_cannot_create_squad(): void
    {
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $response = $this->actingAs($athlete, 'sanctum')
            ->postJson('/api/squads', [
            'name' => 'Juniori',
            'team_id' => $this->team->id,
        ]);

        $response->assertStatus(403);
    }

    // ── Update ──────────────────────────────────────────────────────────────

    public function test_manager_can_update_squad_in_own_club(): void
    {
        $squad = Squad::create(['team_id' => $this->team->id, 'name' => 'Old Name', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/squads/{$squad->id}", [
            'name' => 'New Name',
            'team_id' => $this->team->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_manager_cannot_update_squad_in_other_club(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $squad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Enemy Squad', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/squads/{$squad->id}", [
            'name' => 'Hijacked',
            'team_id' => $otherTeam->id,
        ]);

        $response->assertStatus(403);
    }

    // ── Destroy ─────────────────────────────────────────────────────────────

    public function test_administrator_can_delete_empty_squad(): void
    {
        $squad = Squad::create(['team_id' => $this->team->id, 'name' => 'Empty Squad', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/squads/{$squad->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('squads', ['id' => $squad->id]);
    }

    public function test_cannot_delete_squad_with_members(): void
    {
        $squad = Squad::create(['team_id' => $this->team->id, 'name' => 'Squad With Members', 'created_by' => $this->admin->id]);
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);
        $squad->users()->attach($athlete->id);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/squads/{$squad->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('squads', ['id' => $squad->id]);
    }
}
