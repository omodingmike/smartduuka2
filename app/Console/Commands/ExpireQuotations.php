<?php

namespace App\Console\Commands;

use App\Enums\QuotationStatus;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class ExpireQuotations extends Command
{
    protected $signature = 'quotations:expire';

    protected $description = 'Mark overdue quotation orders as expired';

    public function handle(): void
    {
        Tenant::all()->runForEach(function (Tenant $tenant) {
            Order::query()
                ->where('due_date', '<', now())
                ->where('quotation_status', '<>', QuotationStatus::EXPIRED)
                ->chunkById(100, function ($orders) {
                    foreach ($orders as $order) {
                        $order->update(['quotation_status' => QuotationStatus::EXPIRED]);
                    }
                });
        });
    }
}
