<?php

    namespace App\Services;

    use App\Notifications\LowStockAlert;
    use Illuminate\Support\Facades\Notification;
    use Smartisan\Settings\Facades\Settings;

    class LowStockNotifier
    {
        public static function check(
            string $productName ,
            string $sku ,
            float $currentStock ,
            float $lowStockThreshold ,
            ?string $variationName ,
            ?string $category ,
            string $triggeredBy ,
        ) : void
        {
            if ( $currentStock > $lowStockThreshold ) {
                return;
            }

            $notificationSettings = Settings::group( 'notification' )->all();
            $adminEmail           = $notificationSettings[ 'admin_email' ] ?? NULL;
            $adminPhone           = $notificationSettings[ 'admin_phone' ] ?? NULL;

            $itemLabel = $variationName
                ? "{$productName} — {$variationName}"
                : $productName;

            Notification::route( 'mail' , $adminEmail )
                        ->route( 'sms' , $adminPhone )
                        ->route( 'whatsapp' , $adminPhone )
                        ->notify( new LowStockAlert(
                            title: 'Low Stock Alert' ,
                            message: "Stock for {$itemLabel} has dropped to or below the reorder level." ,
                            productName: $productName ,
                            sku: $sku ,
                            currentStock: $currentStock ,
                            lowStockThreshold: $lowStockThreshold ,
                            variationName: $variationName ,
                            category: $category ,
                            triggeredBy: $triggeredBy ,
                        ) );
        }
    }