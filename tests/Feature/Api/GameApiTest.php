<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Game;
use App\Models\Squad;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameApiTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $admin;
    protected $manager;
    protected $team;
    protected $squad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $this->manager = User::factory()->create(['role' => 'manager', 'club_id' => $this->club->id]);
        $this->team = Team::create(['club_id' => $this->club->id, 'name' => 'Test Team']);
        $this->squad = Squad::create(['team_id' => $this->team->id, 'name' => 'Test Squad', 'created_by' => $this->admin->id]);
    }

    /** Payload for API requests (no club_id / team_id — derived server-side from squad). */
    private function apiPayload(array $overrides = []): array
    {
        return array_merge([
            'squad_id' => $this->squad->id,
            'opponent_name' => 'Rival FC',
            'match_date' => '2026-04-15 18:00:00',
            'location' => 'Home Arena',
        ], $overrides);
    }

    /** Full row for direct Game::create(). */
    private function fullRow(array $overrides = []): array
    {
        return array_merge([
            'club_id' => $this->club->id,
            'team_id' => $this->team->id,
            'squad_id' => $this->squad->id,
            'opponent_name' => 'Rival FC',
            'match_date' => '2026-04-15 18:00:00',
            'location' => 'Home Arena',
        ], $overrides);
    }

    // ── List ────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_games(): void
    {
        $this->getJson('/api/games')->assertStatus(401);
    }

    public function test_manager_can_list_own_club_games(): void
    {
        Game::create($this->fullRow());

        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);
        Game::create($this->fullRow(['club_id' => $otherClub->id, 'team_id' => $otherTeam->id, 'squad_id' => $otherSquad->id]));

        $response = $this->actingAs($this->manager, 'sanctum')->getJson('/api/games');

        $response->assertStatus(200);
        // Manager should only see their club's game
        collect($response->json())->each(fn($g) => $this->assertEquals($this->club->id, $g['club_id']));
    }

    // ── Store ───────────────────────────────────────────────────────────────

    public function test_manager_can_create_game_for_own_club(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/games', $this->apiPayload());

        $response->assertStatus(201);

        $this->assertDatabaseHas('matches', [
            'club_id' => $this->club->id,
            'opponent_name' => 'Rival FC',
        ]);
    }

    public function test_manager_can_create_game_for_any_squad(): void
    {
        // GameController does not restrict squad by club at creation time
        // (club_id is derived from the squad's team's club)
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/games', $this->apiPayload(['squad_id' => $otherSquad->id]));

        $response->assertStatus(201);
    }

    public function test_athlete_cannot_create_game(): void
    {
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $this->actingAs($athlete, 'sanctum')
            ->postJson('/api/games', $this->apiPayload())
            ->assertStatus(403);
    }

    // ── Update ──────────────────────────────────────────────────────────────

    public function test_manager_can_update_own_club_game(): void
    {
        $game = Game::create($this->fullRow());

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/games/{$game->id}", $this->apiPayload(['opponent_name' => 'Updated Rival']));

        $response->assertStatus(200);
        $this->assertDatabaseHas('matches', ['id' => $game->id, 'opponent_name' => 'Updated Rival']);
    }

    public function test_manager_cannot_update_other_club_game(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);
        $game = Game::create($this->fullRow([
            'club_id' => $otherClub->id,
            'team_id' => $otherTeam->id,
            'squad_id' => $otherSquad->id,
        ]));

        $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/games/{$game->id}", $this->apiPayload(['opponent_name' => 'Hacked']))
            ->assertStatus(403);
    }

    // ── Destroy ─────────────────────────────────────────────────────────────

    public function test_manager_can_delete_own_club_game(): void
    {
        $game = Game::create($this->fullRow());

        $this->actingAs($this->manager, 'sanctum')
            ->deleteJson("/api/games/{$game->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('matches', ['id' => $game->id]);
    }

    public function test_manager_cannot_delete_other_club_game(): void
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);
        $game = Game::create($this->fullRow([
            'club_id' => $otherClub->id,
            'team_id' => $otherTeam->id,
            'squad_id' => $otherSquad->id,
        ]));

        $this->actingAs($this->manager, 'sanctum')
            ->deleteJson("/api/games/{$game->id}")
            ->assertStatus(403);
    }
}
