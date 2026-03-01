<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ne asigurăm că există măcar un club pt teste generale
        Club::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_teams(): void
    {
        $response = $this->getJson('/api/teams');
        $response->assertStatus(401);
    }

    public function test_administrator_can_list_all_teams(): void
    {
        $admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);

        $club1 = Club::factory()->create();
        $club2 = Club::factory()->create();

        Team::factory()->create(['club_id' => $club1->id]);
        Team::factory()->create(['club_id' => $club2->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/teams');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_manager_can_only_list_teams_from_own_club(): void
    {
        $club1 = Club::factory()->create();
        $club2 = Club::factory()->create();

        $manager = User::factory()->create(['role' => 'manager', 'club_id' => $club1->id]);

        // Creăm o grupă in clubul managerului
        Team::factory()->create(['club_id' => $club1->id]);
        // Creăm o grupă in alt club
        Team::factory()->create(['club_id' => $club2->id]);

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/teams');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data'); // Ar trebui să vadă doar grupa din clubul lui
    }

    public function test_administrator_can_create_team_with_club_id(): void
    {
        $admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $club = Club::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/teams', [
            'name' => 'Sperante 1',
            'club_id' => $club->id
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Sperante 1');

        $this->assertDatabaseHas('teams', [
            'name' => 'Sperante 1',
            'club_id' => $club->id
        ]);
    }

    public function test_manager_cannot_create_team_for_other_club(): void
    {
        $managerClub = Club::factory()->create();
        $otherClub = Club::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'club_id' => $managerClub->id]);

        // Managerul trimite ID-ul altui club (securitate check), controllerul ii ignora club_id-ul injectandu-l pel al lui
        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/teams', [
            'name' => 'Junior',
            'club_id' => $otherClub->id
        ]);

        $response->assertStatus(201);

        // Numele e salvat dar e la clubul managerului, nu la clubul falsificat
        $this->assertDatabaseHas('teams', [
            'name' => 'Junior',
            'club_id' => $managerClub->id
        ]);

        $this->assertDatabaseMissing('teams', [
            'name' => 'Junior',
            'club_id' => $otherClub->id
        ]);
    }

    public function test_administrator_can_delete_any_empty_team(): void
    {
        $admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $club = Club::factory()->create();
        $team = Team::factory()->create(['club_id' => $club->id]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/teams/' . $team->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_cant_delete_team_with_members(): void
    {
        $admin = User::factory()->create(['role' => 'administrator', 'club_id' => null]);
        $club = Club::factory()->create();
        $team = Team::factory()->create(['club_id' => $club->id]);

        $player = User::factory()->create(['role' => 'sportiv', 'club_id' => $club->id]);
        $player->teams()->attach($team->id); // Asignam un jucator echipei

        $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/teams/' . $team->id);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Grupa trebuie să existe în continuare
        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }
}
