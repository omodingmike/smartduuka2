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
    class ExpenseCreated extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $expenseId ,
            public string $expenseName ,
            public float $amount ,
            public float $baseAmount ,
            public float $extraCharge ,
            public float $paid ,
            public string $date ,
            public string $expenseType ,   // Recurring / Non-Recurring
            public ?string $category ,
            public ?string $note ,
            public string $createdBy ,
        )
        {
            $this->afterCommit();
        }
        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable , 'expense_added' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $unpaid = max( 0 , $this->amount - $this->paid );

            $mail = ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '🧾 New Expense Recorded' )
                ->line( $this->message )
                ->line( "**Expense ID:** {$this->expenseId}" )
                ->line( "**Name:** {$this->expenseName}" )
                ->line( '**Category:** ' . ( $this->category ?? 'N/A' ) )
                ->line( "**Date:** {$this->date}" )
                ->line( "**Type:** {$this->expenseType}" )
                ->line( '---' )
                ->line( '**💰 Amount Breakdown**' )
                ->line( "Base Amount: {$this->baseAmount}" )
                ->line( "Extra Charge: {$this->extraCharge}" )
                ->line( "Total Amount: {$this->amount}" )
                ->line( "Amount Paid: {$this->paid}" )
                ->line( "Outstanding: {$unpaid}" );

            if ( $this->note ) {
                $mail->line( "**Note:** {$this->note}" );
            }

            $mail->line( "**Recorded By:** {$this->createdBy}" );

            return $mail;
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'        => $this->title ,
                'message'      => $this->message ,
                'expense_id'   => $this->expenseId ,
                'expense_name' => $this->expenseName ,
                'amount'       => $this->amount ,
                'base_amount'  => $this->baseAmount ,
                'extra_charge' => $this->extraCharge ,
                'paid'         => $this->paid ,
                'date'         => $this->date ,
                'expense_type' => $this->expenseType ,
                'category'     => $this->category ,
                'note'         => $this->note ,
                'created_by'   => $this->createdBy ,
                'icon'         => '🧾' ,
                'color'        => 'text-orange-500 bg-orange-50 dark:bg-orange-500/10' ,
            ];
        }
    }