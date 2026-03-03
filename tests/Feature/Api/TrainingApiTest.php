<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Location;
use App\Models\Team;
use App\Models\Squad;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $club;
    protected $location;
    protected $team;
    protected $squad;
    protected $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);

        $this->admin = User::factory()->create(['role' => 'administrator']);
        $this->manager = User::factory()->create([
            'role' => 'manager',
            'club_id' => $this->club->id
        ]);

        $this->location = Location::create([
            'club_id' => $this->club->id,
            'name' => 'Test Hall',
            'address' => 'Test St 1'
        ]);

        $this->team = Team::create([
            'club_id' => $this->club->id,
            'name' => 'Test Team'
        ]);

        $this->squad = Squad::create([
            'team_id' => $this->team->id,
            'name' => 'Test Squad',
            'created_by' => $this->admin->id
        ]);

        $this->coach = User::factory()->create([
            'role' => 'antrenor',
            'club_id' => $this->club->id
        ]);
    }

    public function test_admin_can_list_all_trainings()
    {
        Training::create([
            'club_id' => $this->club->id,
            'location_id' => $this->location->id,
            'team_id' => $this->team->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'luni',
            'start_time' => '10:00',
            'end_time' => '12:00'
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/trainings');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_manager_can_only_list_their_club_trainings()
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);
        Training::create([
            'club_id' => $otherClub->id,
            'location_id' => Location::create(['club_id' => $otherClub->id, 'name' => 'Other Hall', 'address' => 'Other St'])->id,
            'team_id' => $otherTeam->id,
            'squad_id' => $otherSquad->id,
            'coach_id' => User::factory()->create(['club_id' => $otherClub->id, 'role' => 'antrenor'])->id,
            'day_of_week' => 'marti',
            'start_time' => '14:00',
            'end_time' => '16:00'
        ]);

        Training::create([
            'club_id' => $this->club->id,
            'location_id' => $this->location->id,
            'team_id' => $this->team->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'luni',
            'start_time' => '10:00',
            'end_time' => '12:00'
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/trainings');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.club_id', $this->club->id);
    }

    public function test_manager_can_create_training_for_their_club()
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/trainings', [
            'location_id' => $this->location->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'miercuri',
            'start_time' => '18:00',
            'end_time' => '20:00'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('trainings', [
            'club_id' => $this->club->id,
            'day_of_week' => 'miercuri'
        ]);
    }

    public function test_training_validation_fails_if_entities_belong_to_another_club()
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/trainings', [
            'location_id' => $this->location->id,
            'squad_id' => $otherSquad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'joi',
            'start_time' => '18:00',
            'end_time' => '20:00'
        ]);

        $response->assertStatus(422);
    }

    public function test_manager_cannot_delete_other_club_training()
    {
        $otherClub = Club::create(['name' => 'Other Club']);
        $otherTeam = Team::create(['club_id' => $otherClub->id, 'name' => 'Other Team']);
        $otherSquad = Squad::create(['team_id' => $otherTeam->id, 'name' => 'Other Squad', 'created_by' => $this->admin->id]);
        $training = Training::create([
            'club_id' => $otherClub->id,
            'location_id' => Location::create(['club_id' => $otherClub->id, 'name' => 'Other Hall', 'address' => 'Other St'])->id,
            'team_id' => $otherTeam->id,
            'squad_id' => $otherSquad->id,
            'coach_id' => User::factory()->create(['club_id' => $otherClub->id, 'role' => 'antrenor'])->id,
            'day_of_week' => 'vineri',
            'start_time' => '14:00',
            'end_time' => '16:00'
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->deleteJson("/api/trainings/{$training->id}");

        $response->assertStatus(403);
    }
}
