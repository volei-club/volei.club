<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
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

    private function createManager($clubId, $email = 'manager@example.com')
    {
        return User::create([
            'name' => 'Manager User',
            'email' => $email,
            'role' => 'manager',
            'club_id' => $clubId,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    private function createAthlete($clubId, $email = 'sportiv@example.com')
    {
        return User::create([
            'name' => 'Sportiv',
            'email' => $email,
            'role' => 'sportiv',
            'club_id' => $clubId,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_administrator_can_list_all_users()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        $this->createManager($club1->id, 'man1@example.com');
        $this->createAthlete($club2->id, 'sport1@example.com');

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 3 total minus the authenticated admin
    }

    public function test_manager_can_only_list_users_from_own_club()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        $manager = $this->createManager($club1->id, 'm1@example.com');
        $this->createAthlete($club1->id, 'a1@example.com'); // same club
        $this->createAthlete($club2->id, 'a2@example.com'); // different club

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // expects only a1 (since query hides caller itself)
            ->assertJsonPath('data.0.email', 'a1@example.com');
    }

    public function test_administrator_can_create_a_manager()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Club 1']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/users', [
            'name' => 'New Manager',
            'email' => 'newm@example.com',
            'role' => 'manager',
            'club_id' => $club->id,
            'password' => 'secure123',
            'is_active' => true
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newm@example.com',
            'club_id' => $club->id,
            'role' => 'manager'
        ]);
    }

    public function test_manager_cannot_create_an_administrator_or_manager()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/users', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'role' => 'administrator',
            'password' => '123456',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_create_an_athlete_in_their_club()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/users', [
            'name' => 'New Youth',
            'email' => 'youth@example.com',
            'role' => 'sportiv',
            'club_id' => null, // Trying to pass null or something else, it should force the manager's club_id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'youth@example.com',
            'club_id' => $club->id, // It must auto-bind to the manager's club
            'role' => 'sportiv'
        ]);
    }

    public function test_manager_cannot_edit_user_from_another_club()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        $manager = $this->createManager($club1->id);
        $athlete = $this->createAthlete($club2->id);

        $response = $this->actingAs($manager, 'sanctum')->putJson("/api/users/{$athlete->id}", [
            'name' => 'Renamed',
            'email' => $athlete->email,
            'role' => 'sportiv',
            'club_id' => $club2->id
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_delete_administrator()
    {
        $club = Club::create(['name' => 'Club 1']);
        $admin = $this->createAdmin();
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403);
    }

    public function test_manager_can_delete_athlete_from_own_club()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);
        $athlete = $this->createAthlete($club->id);

        $response = $this->actingAs($manager, 'sanctum')->deleteJson("/api/users/{$athlete->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $athlete->id
        ]);
    }

    public function test_user_cannot_delete_themselves()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Nu vă puteți șterge propriul cont.']);
    }
}
