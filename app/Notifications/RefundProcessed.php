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
    class RefundProcessed extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $returnOrderNo ,
            public string $originalOrderNo ,
            public ?string $customerName ,
            public string $createdBy ,
            public string $orderDate ,
            public float $totalReturnValue ,
            public float $totalExchangeValue ,
            public float $refundBalance ,       // returnValue - exchangeValue
            public int $returnItemCount ,
            public int $exchangeItemCount ,
            public string $refundStatus ,
            public string $returnStatus ,
            public ?string $reason ,
        )
        {
            $this->afterCommit();
        }

        public function via(object $notifiable) : array
        {
            $settings    = Settings::group( 'notification' )->all();
            $channels    = [];
            $events      = $settings[ 'events' ] ?? [];
            $eventConfig = collect( $events )->firstWhere( 'id' , 'refund_processed' );

            if ( $eventConfig && isset( $eventConfig[ 'channels' ] ) ) {
                $channelMap = [
                    'email'    => 'mail' ,
                    'sms'      => 'sms' ,
                    'whatsapp' => 'whatsapp' ,
                    'system'   => 'database' ,
                ];
                foreach ( $channelMap as $settingKey => $laravelChannel ) {
                    if ( ! empty( $eventConfig[ 'channels' ][ $settingKey ] ) ) {
                        $channels[] = $laravelChannel;
                    }
                }
            }

            return ! empty( $channels ) ? $channels : [ 'mail' ];
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $mail = ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '↩️ Refund / Return Processed' )
                ->line( $this->message )
                ->line( "**Return Order #:** {$this->returnOrderNo}" )
                ->line( "**Original Order #:** {$this->originalOrderNo}" )
                ->line( '**Customer:** ' . ( $this->customerName ?? 'Walk-in' ) )
                ->line( "**Processed By:** {$this->createdBy}" )
                ->line( "**Date:** {$this->orderDate}" )
                ->line( '---' )
                ->line( '**📦 Return Summary**' )
                ->line( "Items Returned: {$this->returnItemCount}" )
                ->line( "Return Value: {$this->totalReturnValue}" );

            if ( $this->exchangeItemCount > 0 ) {
                $mail->line( '---' )
                     ->line( '**🔄 Exchange Summary**' )
                     ->line( "Items Exchanged: {$this->exchangeItemCount}" )
                     ->line( "Exchange Value: {$this->totalExchangeValue}" );
            }

            $mail->line( '---' )
                 ->line( '**💰 Refund Balance**' )
                 ->line( "Net Refund Due: {$this->refundBalance}" )
                 ->line( "Refund Status: {$this->refundStatus}" )
                 ->line( "Return Status: {$this->returnStatus}" );

            if ( $this->reason ) {
                $mail->line( "**Reason:** {$this->reason}" );
            }

            return $mail;
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'                => $this->title ,
                'message'              => $this->message ,
                'return_order_no'      => $this->returnOrderNo ,
                'original_order_no'    => $this->originalOrderNo ,
                'customer_name'        => $this->customerName ,
                'created_by'           => $this->createdBy ,
                'order_date'           => $this->orderDate ,
                'total_return_value'   => $this->totalReturnValue ,
                'total_exchange_value' => $this->totalExchangeValue ,
                'refund_balance'       => $this->refundBalance ,
                'return_item_count'    => $this->returnItemCount ,
                'exchange_item_count'  => $this->exchangeItemCount ,
                'refund_status'        => $this->refundStatus ,
                'return_status'        => $this->returnStatus ,
                'reason'               => $this->reason ,
                'category'             => 'Sales' ,
                'icon'                 => '↩️' ,
                'color'                => 'text-yellow-500 bg-yellow-50 dark:bg-yellow-500/10' ,
            ];
        }
    }