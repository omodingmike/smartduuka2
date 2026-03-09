<?php

    namespace App\Console\Commands;

    use App\Enums\PreOrderStatus;
    use App\Models\Order;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Log;

    class UpdatePreOrderStatuses extends Command
    {
        protected $signature   = 'app:update-pre-order-statuses';
        protected $description = 'Update pre-order statuses from Pending Stock to Ready for Pickup if stock is available.';

        public function handle()
        {
            $this->info( 'Checking pre-orders for status updates...' );

            $pre_orders = Order::with( 'orderProducts.item' )
                               ->where( 'pre_order_status' , PreOrderStatus::PENDING_STOCK )
                               ->get();

            foreach ( $pre_orders as $pre_order ) {
                $allProductsHaveEnoughStock = TRUE;
                foreach ( $pre_order->orderProducts as $orderProduct ) {
                    if ( ! $orderProduct->item || $orderProduct->item->stock < $orderProduct->quantity ) {
                        $allProductsHaveEnoughStock = FALSE;
                        break;
                    }
                }

                if ( $allProductsHaveEnoughStock ) {
                    $pre_order->update( [ 'pre_order_status' => PreOrderStatus::READY_FOR_PICKUP ] );
                    $this->info( "Order #{$pre_order->id} status updated to Ready for Pickup." );
                    Log::info( "Order #{$pre_order->id} status updated to Ready for Pickup." );
                }
            }

            $this->info( 'Pre-order status update check complete.' );
        }
    }
