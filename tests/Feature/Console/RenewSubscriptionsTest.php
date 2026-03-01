<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class RenewSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_renew_subscriptions_command_renews_expired_paid_subscriptions()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $club->id]);
        $subDef = Subscription::factory()->create([
            'club_id' => $club->id,
            'period' => '1_luna' // Default renewal logic adds 1 month
        ]);

        // Create an active paid subscription that expired yesterday
        $expiredSub = UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => Carbon::yesterday()->subMonth(),
            'expires_at' => Carbon::yesterday(),
            'status' => 'active_paid'
        ]);

        // Run the command
        $this->artisan('app:renew-subscriptions')
            ->expectsOutput('Starting subscription renewals...')
            ->expectsOutput("Renewed subscription for User ID {$athlete->id}. Old expired, new pending created.")
            ->expectsOutput('Successfully processed 1 subscription renewals.')
            ->assertExitCode(0);

        // Verify old subscription is marked as inactive_expired
        $this->assertDatabaseHas('user_subscriptions', [
            'id' => $expiredSub->id,
            'status' => 'inactive_expired'
        ]);

        // Verify a new pending subscription was created starting today
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'status' => 'active_pending',
            'starts_at' => Carbon::yesterday()->addDay()->format('Y-m-d H:i:s'),
            'expires_at' => Carbon::yesterday()->addDay()->addMonth()->subDay()->format('Y-m-d H:i:s')
        ]);
    }

    public function test_renew_subscriptions_calculates_correct_period_for_two_weeks()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $club->id]);
        $subDef = Subscription::factory()->create([
            'club_id' => $club->id,
            'period' => '2_saptamani'
        ]);

        UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => Carbon::today()->subWeeks(2),
            'expires_at' => Carbon::today(),
            'status' => 'active_paid'
        ]);

        $this->artisan('app:renew-subscriptions')->assertExitCode(0);

        // New sub should be 2 weeks long
        $newSub = UserSubscription::where('status', 'active_pending')->first();
        $this->assertNotNull($newSub);

        $start = Carbon::parse($newSub->starts_at);
        $end = Carbon::parse($newSub->expires_at);

        // Command logic: $newExpiresAt->addWeeks(2)->subDay();
        // starts_at is yesterday->expires_at + 1 day = today
        // expires_at is starts_at + 2 weeks - 1 day
        $this->assertEquals($start->copy()->addWeeks(2)->subDay()->toDateString(), $end->toDateString());
    }

    public function test_renew_subscriptions_ignores_active_pending()
    {
        $club = Club::create(['name' => 'Club 1']);
        $athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $club->id]);
        $subDef = Subscription::factory()->create(['club_id' => $club->id]);

        UserSubscription::create([
            'user_id' => $athlete->id,
            'subscription_id' => $subDef->id,
            'starts_at' => Carbon::yesterday()->subMonth(),
            'expires_at' => Carbon::yesterday(),
            'status' => 'active_pending' // NOT active_paid
        ]);

        $this->artisan('app:renew-subscriptions')
            ->expectsOutput('Successfully processed 0 subscription renewals.')
            ->assertExitCode(0);
    }
}
