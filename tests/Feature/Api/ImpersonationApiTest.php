<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Squad;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationApiTest extends TestCase
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

    public function test_administrator_can_impersonate_another_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/impersonate/{$this->athlete->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'token']);
    }

    public function test_manager_cannot_impersonate(): void
    {
        $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/impersonate/{$this->athlete->id}")
            ->assertStatus(403);
    }

    public function test_athlete_cannot_impersonate(): void
    {
        $this->actingAs($this->athlete, 'sanctum')
            ->postJson("/api/impersonate/{$this->manager->id}")
            ->assertStatus(403);
    }

    public function test_administrator_cannot_impersonate_themselves(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/impersonate/{$this->admin->id}")
            ->assertStatus(400);
    }

    public function test_unauthenticated_user_cannot_impersonate(): void
    {
        $this->postJson("/api/impersonate/{$this->athlete->id}")
            ->assertStatus(401);
    }

    public function test_impersonate_nonexistent_user_returns_404(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000099';

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/impersonate/{$fakeId}")
            ->assertStatus(404);
    }
}
