<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Enums\SubscriptionPaymentStatus;
use App\Models\TenantSubscription;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';

    protected $description = 'Deactivate subscriptions that have passed their expiry date';

    public function handle(): void
    {
        $expiredTenantIds = TenantSubscription::query()
            ->where('expires_at', '<', now())
            ->where('status', Status::ACTIVE)
            ->pluck('tenant_id');

        if ($expiredTenantIds->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return;
        }

        $updated = TenantSubscription::query()
            ->whereIn('tenant_id', $expiredTenantIds)
            ->where('payment_status', SubscriptionPaymentStatus::Paid)
            ->where('status', Status::ACTIVE)
            ->update(['status' => Status::INACTIVE]);

        $this->info("Deactivated {$updated} subscription(s).");
    }
}
