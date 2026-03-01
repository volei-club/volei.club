<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
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
        ]);
    }

    public function test_administrator_can_see_all_locations()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        Location::create(['name' => 'Loc 1', 'address' => 'Addr 1', 'club_id' => $club1->id]);
        Location::create(['name' => 'Loc 2', 'address' => 'Addr 2', 'club_id' => $club2->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/locations');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_manager_can_only_see_own_club_locations()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);

        Location::create(['name' => 'Loc 1', 'address' => 'Addr 1', 'club_id' => $club1->id]);
        Location::create(['name' => 'Loc 2', 'address' => 'Addr 2', 'club_id' => $club2->id]);

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/locations');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Loc 1']);
    }

    public function test_manager_can_create_location_for_own_club()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/locations', [
            'name' => 'New Location',
            'address' => 'New Address'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('locations', [
            'name' => 'New Location',
            'club_id' => $club->id
        ]);
    }

    public function test_manager_cannot_update_other_club_location()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);
        $location = Location::create(['name' => 'Loc 2', 'address' => 'Addr 2', 'club_id' => $club2->id]);

        $response = $this->actingAs($manager, 'sanctum')->putJson("/api/locations/{$location->id}", [
            'name' => 'Updated Name',
            'address' => 'Updated Addr'
        ]);

        $response->assertStatus(403);
    }

    public function test_location_changes_are_audited()
    {
        $club = Club::create(['name' => 'Club 1']);
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/locations', [
            'name' => 'Audit Me',
            'address' => 'Some Address',
            'club_id' => $club->id
        ]);

        $locationId = $response->json('id');

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'App\\Models\\Location',
            'auditable_id' => $locationId,
            'event' => 'created',
            'club_id' => $club->id
        ]);
    }
}
