<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class UserSubscriptionApiTest extends TestCase
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

    public function test_manager_can_assign_subscription_to_athlete_in_club()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);
        $athlete = $this->createAthlete($club->id);
        $subDef = Subscription::factory()->create(['club_id' => $club->id, 'period' => '1_luna']);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/user-subscriptions', [
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => 'active_pending'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'status' => 'active_pending'
        ]);

        // Verify calculation
        $record = UserSubscription::first();
        $this->assertEquals(Carbon::parse($record->starts_at)->addMonth()->format('Y-m-d'), Carbon::parse($record->expires_at)->format('Y-m-d'));
    }

    public function test_manager_cannot_assign_to_athlete_in_different_club()
    {
        $club1 = Club::create(['name' => 'Club 1']);
        $club2 = Club::create(['name' => 'Club 2']);
        $manager = $this->createManager($club1->id);
        $athlete = $this->createAthlete($club2->id); // Other club
        $subDef = Subscription::factory()->create(['club_id' => $club1->id]);

        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/user-subscriptions', [
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => 'active_pending'
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_update_subscription_status()
    {
        $club = Club::create(['name' => 'Club 1']);
        $manager = $this->createManager($club->id);
        $athlete = $this->createAthlete($club->id);
        $subDef = Subscription::factory()->create(['club_id' => $club->id]);

        $userSub = UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'status' => 'active_pending'
        ]);

        $response = $this->actingAs($manager, 'sanctum')->patchJson("/api/user-subscriptions/{$userSub->id}/status", [
            'status' => 'active_paid'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_subscriptions', [
            'id' => $userSub->id,
            'status' => 'active_paid'
        ]);
    }

    public function test_user_active_subscription_relationship()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = $this->createAthlete($club->id);
        $subDef = Subscription::factory()->create(['club_id' => $club->id]);

        // Create an old expired subscription
        UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subMonth(),
            'status' => 'expired'
        ]);

        // Create an active one
        $active = UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'status' => 'active_paid'
        ]);

        $athlete->refresh();
        $this->assertEquals($active->id, $athlete->activeSubscription->id);
    }

    public function test_user_history_includes_all_subscriptions()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = $this->createAthlete($club->id);
        $subDef = Subscription::factory()->create(['club_id' => $club->id]);

        UserSubscription::factory()->count(3)->create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id
        ]);

        $athlete->refresh();
        $this->assertCount(3, $athlete->subscriptions);
    }
}
