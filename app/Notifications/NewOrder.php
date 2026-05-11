<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Notifications\Notification;
    use Illuminate\Queue\Attributes\MaxExceptions;
    use Illuminate\Queue\Attributes\Timeout;
    use Illuminate\Queue\Attributes\Tries;

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

        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable , 'new_order' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            return ( new MailMessage )
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
                ->line( "Balance Due: {$this->balance}" )
                ->lineIf( $this->change > 0 , "Change Given: {$this->change}" );
        }

        public function toArray(object $notifiable) : array
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