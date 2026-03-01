<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleSecurityApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole($role)
    {
        $club = Club::firstOrCreate(['name' => 'Security Club']);
        return User::create([
            'name' => 'Test User ' . $role,
            'email' => $role . '@example.com',
            'role' => $role,
            'club_id' => $club->id,
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_sportiv_cannot_access_user_list()
    {
        $sportiv = $this->createUserWithRole('sportiv');
        $this->actingAs($sportiv, 'sanctum')->getJson('/api/users')->assertStatus(403);
    }

    public function test_sportiv_cannot_create_user()
    {
        $sportiv = $this->createUserWithRole('sportiv');
        $this->actingAs($sportiv, 'sanctum')->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'role' => 'sportiv',
        ])->assertStatus(403);
    }

    public function test_sportiv_cannot_delete_user()
    {
        $sportiv = $this->createUserWithRole('sportiv');
        $target = $this->createUserWithRole('parinte');
        $this->actingAs($sportiv, 'sanctum')->deleteJson('/api/users/' . $target->id)->assertStatus(403);
    }

    public function test_antrenor_cannot_create_teams()
    {
        $antrenor = $this->createUserWithRole('antrenor');
        $this->actingAs($antrenor, 'sanctum')->postJson('/api/teams', [
            'name' => 'New Team',
        ])->assertStatus(403);
    }

    public function test_parinte_cannot_update_teams()
    {
        $parinte = $this->createUserWithRole('parinte');
        $team = \App\Models\Team::create(['name' => 'Test Team', 'club_id' => $parinte->club_id]);

        $this->actingAs($parinte, 'sanctum')->putJson('/api/teams/' . $team->id, [
            'name' => 'Hacked Team Name',
        ])->assertStatus(403);
    }

    public function test_manager_cannot_impersonate_user()
    {
        $manager = $this->createUserWithRole('manager');
        $target = $this->createUserWithRole('sportiv');

        $this->actingAs($manager, 'sanctum')->postJson('/api/impersonate/' . $target->id)
            ->assertStatus(403);
    }

    public function test_administrator_can_impersonate_user()
    {
        $admin = $this->createUserWithRole('administrator');
        $target = $this->createUserWithRole('manager');

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/impersonate/' . $target->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'token']);
    }
}
