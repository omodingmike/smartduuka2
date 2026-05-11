<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Notifications\Notification;
    use Illuminate\Queue\Attributes\MaxExceptions;
    use Illuminate\Queue\Attributes\Timeout;
    use Illuminate\Queue\Attributes\Tries;
    use Smartisan\Settings\Facades\Settings;

    #[Tries( 5 )]
    #[Timeout( 120 )]
    #[MaxExceptions( 3 )]
    class LowStockAlert extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $productName ,
            public string $sku ,
            public float $currentStock ,
            public float $lowStockThreshold ,
            public ?string $variationName ,      // null for simple products
            public ?string $category ,
            public string $triggeredBy ,        // 'product_created' | 'variation_created'
        )
        {
            $this->afterCommit();
        }
        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable , 'low_stock' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $itemLabel = $this->variationName
                ? "{$this->productName} — {$this->variationName}"
                : $this->productName;

            return ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '⚠️ Low Stock Alert' )
                ->line( $this->message )
                ->line( "**Product:** {$itemLabel}" )
                ->line( "**SKU:** {$this->sku}" )
                ->line( '**Category:** ' . ( $this->category ?? 'N/A' ) )
                ->line( '---' )
                ->line( '**📦 Stock Levels**' )
                ->line( "Current Stock: {$this->currentStock}" )
                ->line( "Low Stock Threshold: {$this->lowStockThreshold}" )
                ->line( '⚠️ Stock is at or below the reorder level. Please restock soon.' );
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'               => $this->title ,
                'message'             => $this->message ,
                'product_name'        => $this->productName ,
                'sku'                 => $this->sku ,
                'current_stock'       => $this->currentStock ,
                'low_stock_threshold' => $this->lowStockThreshold ,
                'variation_name'      => $this->variationName ,
                'category'            => $this->category ,
                'triggered_by'        => $this->triggeredBy ,
                'icon'                => '⚠️' ,
                'color'               => 'text-red-500 bg-red-50 dark:bg-red-500/10' ,
            ];
        }
    }