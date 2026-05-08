<?php

namespace App\Console\Commands;

use App\Enums\PreOrderStatus;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class UpdatePreOrderStock extends Command
{
    protected $signature = 'pre-orders:update-stock';

    protected $description = 'Mark pre-orders as ready for pickup when all items have sufficient stock';

    public function handle(): void
    {
        Tenant::all()->runForEach(function (Tenant $tenant) {
            Order::query()
                ->where('pre_order_status', PreOrderStatus::PENDING_STOCK)
                ->with('orderProducts.item')
                ->chunk(100, function ($orders) {
                    foreach ($orders as $order) {
                        $this->checkAndUpdatePreOrder($order);
                    }
                });
        });
    }

    private function checkAndUpdatePreOrder(Order $order): void
    {
        $allInStock = $order->orderProducts->every(
            fn ($orderProduct) => $orderProduct->item->stock >= $orderProduct->quantity
        );

        if ($allInStock) {
            $order->update(['pre_order_status' => PreOrderStatus::READY_FOR_PICKUP]);
        }
    }
}
