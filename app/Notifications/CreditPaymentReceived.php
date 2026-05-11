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
    class CreditPaymentReceived extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title,
            public string $message,
            public string $orderNo,
            public string $customerName,
            public float  $amountPaid,
            public float  $remainingBalance,
            public bool   $fullySettled,
        ) {
            $this->afterCommit();
        }
        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable,'credit_payment' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $mail = ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '💳 Credit Payment Received' )
                ->line( $this->message )
                ->line( "**Order:** #{$this->orderNo}" )
                ->line( "**Customer:** {$this->customerName}" )
                ->line( "**Amount Paid:** {$this->amountPaid}" )
                ->line( "**Remaining Balance:** {$this->remainingBalance}" );

            if ( $this->fullySettled ) {
                $mail->line( '✅ This order has been **fully settled**. Payment status updated to Paid.' );
            }
            else {
                $mail->line( '⚠️ This order still has an outstanding balance.' );
            }

            return $mail;
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'             => $this->title ,
                'message'           => $this->message ,
                'order_no'          => $this->orderNo ,
                'customer_name'     => $this->customerName ,
                'amount_paid'       => $this->amountPaid ,
                'remaining_balance' => $this->remainingBalance ,
                'fully_settled'     => $this->fullySettled ,
                'category'          => 'Finance' ,
                'icon'              => '💳' ,
                'color'             => 'text-green-500 bg-green-50 dark:bg-green-500/10' ,
            ];
        }
    }