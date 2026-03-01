<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RenewSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:renew-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew active paid subscriptions based on their specific period and create new billing historical records.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription renewals...');

        // Find subscriptions that expire today or have already expired but are still active_paid
        $expiringSubscriptions = UserSubscription::where('status', 'active_paid')
            ->whereDate('expires_at', '<=', Carbon::today())
            ->with('subscription')
            ->get();

        $count = 0;

        foreach ($expiringSubscriptions as $currentSub) {
            $baseSub = $currentSub->subscription;

            if (!$baseSub) {
                Log::warning("Subscription definition missing for UserSubscription ID: {$currentSub->id}");
                continue;
            }

            // Calculate new validity range
            $newStartsAt = Carbon::parse($currentSub->expires_at)->addDay();
            $newExpiresAt = clone $newStartsAt;

            switch ($baseSub->period) {
                case '1_saptamana':
                    $newExpiresAt->addWeeks(1)->subDay(); // E.g., Monday through Sunday
                    break;
                case '2_saptamani':
                    $newExpiresAt->addWeeks(2)->subDay();
                    break;
                case '1_luna':
                    $newExpiresAt->addMonth()->subDay();
                    break;
                case '3_luni':
                    $newExpiresAt->addMonths(3)->subDay();
                    break;
                case '6_luni':
                    $newExpiresAt->addMonths(6)->subDay();
                    break;
                case '1_an':
                    $newExpiresAt->addYear()->subDay();
                    break;
                default:
                    $newExpiresAt->addMonth()->subDay();
                    break;
            }

            // Create the new historical billing period using the active_pending status
            UserSubscription::create([
                'user_id' => $currentSub->user_id,
                'subscription_id' => $currentSub->subscription_id,
                'starts_at' => $newStartsAt,
                'expires_at' => $newExpiresAt,
                'status' => 'active_pending'
            ]);

            // Mark the old one as inactive_expired
            $currentSub->update(['status' => 'inactive_expired']);

            $count++;
            $this->info("Renewed subscription for User ID {$currentSub->user_id}. Old expired, new pending created.");
        }

        $this->info("Successfully processed {$count} subscription renewals.");
    }
}
