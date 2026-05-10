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
    class NewOrder extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $orderNo ,
            public string $orderStatus ,
            public string $paymentStatus ,
            public string $paymentType ,
            public float $total ,
            public float $paid ,
            public float $balance ,
            public float $change ,
            public ?string $customerName ,
            public string $createdBy ,
            public string $orderDate ,
            public int $itemCount ,
        )
        {
            $this->afterCommit();
        }

        public function routeNotificationForMail($notifiable)
        {
            $settings = Settings::group( 'notification' )->all();
            return $settings[ 'admin_email' ] ?? NULL;
        }

        public function routeNotificationForSms($notifiable)
        {
            $settings = Settings::group( 'notification' )->all();
            return $settings[ 'admin_phone' ] ?? NULL;
        }

        public function routeNotificationForWhatsapp($notifiable)
        {
            $settings = Settings::group( 'notification' )->all();
            return $settings[ 'admin_phone' ] ?? NULL;
        }

        public function via(object $notifiable) : array
        {
            $settings = Settings::group( 'notification' )->all();
            $channels = [];
//            $events      = $settings[ 'events' ] ?? [];
            $events      = $settings[ 'events' ] ?? [];
            $eventConfig = collect( $events )->firstWhere( 'id' , 'new_order' );

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

            return ! empty( $channels ) ? $channels : [ 'database' ];
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $mail = ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '🛒 New Sale / Order' )
                ->line( $this->message )
                ->line( "**Order #:** {$this->orderNo}" )
                ->line( "**Date:** {$this->orderDate}" )
                ->line( '**Customer:** ' . ( $this->customerName ?? 'Walk-in' ) )
                ->line( "**Served By:** {$this->createdBy}" )
                ->line( '---' )
                ->line( '**🧾 Order Details**' )
                ->line( "Items: {$this->itemCount}" )
                ->line( "Order Status: {$this->orderStatus}" )
                ->line( "Payment Type: {$this->paymentType}" )
                ->line( "Payment Status: {$this->paymentStatus}" )
                ->line( '---' )
                ->line( '**💰 Payment Summary**' )
                ->line( "Total: {$this->total}" )
                ->line( "Amount Paid: {$this->paid}" )
                ->line( "Balance Due: {$this->balance}" );

            if ( $this->change > 0 ) {
                $mail->line( "Change Given: {$this->change}" );
            }

            return $mail;
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'          => $this->title ,
                'message'        => $this->message ,
                'order_no'       => $this->orderNo ,
                'order_status'   => $this->orderStatus ,
                'payment_status' => $this->paymentStatus ,
                'payment_type'   => $this->paymentType ,
                'total'          => $this->total ,
                'paid'           => $this->paid ,
                'balance'        => $this->balance ,
                'change'         => $this->change ,
                'customer_name'  => $this->customerName ,
                'created_by'     => $this->createdBy ,
                'order_date'     => $this->orderDate ,
                'item_count'     => $this->itemCount ,
                'category'       => 'Sales' ,
                'icon'           => '🛒' ,
                'color'          => 'text-blue-500 bg-blue-50 dark:bg-blue-500/10' ,
            ];
        }
    }