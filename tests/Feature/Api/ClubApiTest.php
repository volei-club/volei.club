<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClubApiTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    private function createManager($clubId)
    {
        return User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'club_id' => $clubId,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_administrator_can_create_a_club()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/clubs', [
            'name' => 'Test Volei Club',
        ]);

        $response->assertStatus(201)
            ->assertJson([
            'status' => 'success',
            'data' => [
                'name' => 'Test Volei Club',
            ]
        ]);

        $this->assertDatabaseHas('clubs', [
            'name' => 'Test Volei Club',
            'created_by' => $admin->id,
        ]);
    }

    public function test_manager_cannot_create_a_club()
    {
        // First we need at least one club to assign the manager to
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Base Club', 'created_by' => $admin->id]);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/clubs', [
            'name' => 'Hacked Volei Club',
        ]);

        $response->assertStatus(403);
    }

    public function test_administrator_can_list_all_clubs()
    {
        $admin = $this->createAdmin();
        Club::create(['name' => 'Club Alpha', 'created_by' => $admin->id]);
        Club::create(['name' => 'Club Beta', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/clubs');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_manager_can_only_list_own_club()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club Alpha', 'created_by' => $admin->id]);
        $club2 = Club::create(['name' => 'Club Beta', 'created_by' => $admin->id]);

        $manager = $this->createManager($club1->id);

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/clubs');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $club1->id);
    }

    public function test_administrator_can_update_a_club()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Old Name', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/clubs/{$club->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'name' => 'New Name'
        ]);
    }

    public function test_manager_cannot_update_a_club()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Old Name', 'created_by' => $admin->id]);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->putJson("/api/clubs/{$club->id}", [
            'name' => 'New Name By Manager',
        ]);

        $response->assertStatus(403);
    }

    public function test_administrator_can_delete_an_empty_club()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Empty Club', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/clubs/{$club->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('clubs', [
            'id' => $club->id
        ]);
    }

    public function test_administrator_cannot_delete_a_club_with_users()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Populated Club', 'created_by' => $admin->id]);

        // Add a user to it
        $this->createManager($club->id);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/clubs/{$club->id}");

        $response->assertStatus(422) // handled in ClubController
            ->assertJson([
            'status' => 'error'
        ]);

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id
        ]);
    }
}
