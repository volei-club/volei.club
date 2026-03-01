<?php

namespace Tests\Feature\Api;

use App\Models\AuditLog;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditApiTest extends TestCase
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

    public function test_administrator_can_see_all_audit_logs()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        // Clean up logs created during setup to have a deterministic test
        AuditLog::query()->delete();

        // Create some logs
        AuditLog::create([
            'auditable_type' => 'App\\Models\\Club',
            'auditable_id' => $club1->id,
            'event' => 'created',
            'club_id' => $club1->id,
            'new_values' => ['name' => 'Club 1']
        ]);

        AuditLog::create([
            'auditable_type' => 'App\\Models\\Club',
            'auditable_id' => $club2->id,
            'event' => 'created',
            'club_id' => $club2->id,
            'new_values' => ['name' => 'Club 2']
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/audit');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_manager_can_only_see_own_club_audit_logs()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);

        // Clean up logs created during setup
        AuditLog::query()->delete();

        // Log of club 1
        AuditLog::create([
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => \Illuminate\Support\Str::uuid(),
            'event' => 'created',
            'club_id' => $club1->id,
            'new_values' => ['name' => 'User 1']
        ]);

        // Log of club 2
        AuditLog::create([
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => \Illuminate\Support\Str::uuid(),
            'event' => 'created',
            'club_id' => $club2->id,
            'new_values' => ['name' => 'User 2']
        ]);

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/audit');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($club1->id, $response->json('data.0.club_id'));
    }

    public function test_auditable_trait_captures_club_id_from_model()
    {
        $club = Club::create(['name' => 'Club 1']);
        $admin = $this->createAdmin();

        // Acting as admin, create a User (Auditable)
        $this->actingAs($admin, 'sanctum')->postJson('/api/users', [
            'name' => 'New Athlete',
            'email' => 'athlete@club1.com',
            'role' => 'sportiv',
            'club_id' => $club->id,
            'password' => 'password123',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'App\\Models\\User',
            'event' => 'created',
            'club_id' => $club->id // Should be captured from the model's club_id
        ]);
    }
}
