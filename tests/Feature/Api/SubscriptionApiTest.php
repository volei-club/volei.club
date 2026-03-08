<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
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

    public function test_administrator_can_list_all_subscriptions()
    {
        $admin = $this->createAdmin();
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);

        Subscription::factory()->create(['club_id' => $club1->id]);
        Subscription::factory()->create(['club_id' => $club2->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/subscriptions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_manager_can_only_list_own_club_subscriptions()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);

        Subscription::factory()->count(2)->create(['club_id' => $club1->id]);
        Subscription::factory()->create(['club_id' => $club2->id]);

        $response = $this->actingAs($manager, 'sanctum')->getJson('/api/subscriptions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_administrator_can_create_subscription()
    {
        $admin = $this->createAdmin();
        $club = Club::create(['name' => 'Club 1']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/subscriptions', [
            'club_id' => $club->id,
            'name' => 'Abonament VIP',
            'price' => 200,
            'period' => '1_luna'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Abonament VIP',
            'club_id' => $club->id
        ]);
    }

    public function test_manager_can_create_subscription_for_own_club()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/subscriptions', [
            'name' => 'Abonament Manager',
            'price' => 150,
            'period' => '3_luni'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Abonament Manager',
            'club_id' => $club->id
        ]);
    }

    public function test_manager_cannot_create_subscription_for_another_club()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);

        // Even if they try to pass club_id, the controller should use their own
        // But store() logic check: $clubId = $user->role === 'manager' ? $user->club_id : $request->club_id;
        // So they CAN try, but it will be ignored.
        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/subscriptions', [
            'club_id' => $club2->id,
            'name' => 'Hacker Sub',
            'price' => 10,
            'period' => '1_an'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Hacker Sub',
            'club_id' => $club1->id // Should be theirs
        ]);
        $this->assertDatabaseMissing('subscriptions', [
            'name' => 'Hacker Sub',
            'club_id' => $club2->id
        ]);
    }

    public function test_athlete_cannot_access_subscriptions()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = $this->createAthlete($club->id);

        $response = $this->actingAs($athlete, 'sanctum')->getJson('/api/subscriptions');

        $response->assertStatus(403);
    }

    public function test_subscription_update()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);
        $sub = Subscription::factory()->create(['club_id' => $club->id, 'name' => 'Old Name']);

        $response = $this->actingAs($manager, 'sanctum')->putJson("/api/subscriptions/{$sub->id}", [
            'name' => 'New Name',
            'price' => 99,
            'period' => '1_luna'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'name' => 'New Name'
        ]);
    }

    public function test_subscription_deletion_fails_if_in_use()
    {
        $club = Club::create(['name' => 'Club 1']);
        $admin = $this->createAdmin();
        $sub = Subscription::factory()->create(['club_id' => $club->id]);

        // Mock a user using it
        \App\Models\UserSubscription::create([
            'user_id' => $this->createAthlete($club->id)->id,
            'subscription_id' => $sub->id,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'status' => 'active_paid'
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/subscriptions/{$sub->id}");

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('api_subscriptions.in_use_error')]);
    }

    public function test_subscription_deletion_success()
    {
        $club = Club::create(['name' => 'Club 1']);
        $admin = $this->createAdmin();
        $sub = Subscription::factory()->create(['club_id' => $club->id]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/subscriptions/{$sub->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('subscriptions', ['id' => $sub->id]);
    }
}
