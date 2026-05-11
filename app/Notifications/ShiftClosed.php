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
    class ShiftClosed extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $closedBy ,
            public string $closedAt ,
            public float $openingFloat ,
            public float $expectedFloat ,
            public float $closingFloat ,
            public float $discrepancy ,
            public float $totalSalesValue ,   // All items sold (cash + credit)
            public float $totalRevenue ,      // Cash actually received
            public float $totalCostOfGoods ,
            public float $grossProfit ,
            public float $expenses ,
            public float $netProfit ,
            public float $totalCredit ,       // Outstanding credit balance
            public float $deposits ,
            public float $walletTransactions ,
        )
        {
            $this->afterCommit();
        }
        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable , 'shift_closed' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            $discrepancyLabel = match ( TRUE ) {
                $this->discrepancy > 0 => "+{$this->discrepancy} (Surplus)" ,
                $this->discrepancy < 0 => abs( $this->discrepancy ) . ' (Shortage)' ,
                default                => 'Balanced'
            };

            return ( new MailMessage )
                ->subject( $this->title )
                ->greeting( '🧾 End of Shift Summary' )
                ->line( $this->message )
                ->line( "**Closed By:** {$this->closedBy}" )
                ->line( "**Closed At:** {$this->closedAt}" )
                ->line( '---' )
                ->line( '**📦 Sales Performance**' )
                ->line( "Total Sales Value (Cash + Credit): {$this->totalSalesValue}" )
                ->line( "Total Revenue Collected: {$this->totalRevenue}" )
                ->line( "Cost of Goods Sold: {$this->totalCostOfGoods}" )
                ->line( "Gross Profit: {$this->grossProfit}" )
                ->line( "Operational Expenses: {$this->expenses}" )
                ->line( "Net Profit: {$this->netProfit}" )
                ->line( '---' )
                ->line( '**💰 Cash Drawer**' )
                ->line( "Opening Float: {$this->openingFloat}" )
                ->line( "Expected Float: {$this->expectedFloat}" )
                ->line( "Actual Closing Float: {$this->closingFloat}" )
                ->line( "Discrepancy: {$discrepancyLabel}" )
                ->line( '---' )
                ->line( '**📒 Credit & Deposits**' )
                ->line( "Outstanding Credit Balance: {$this->totalCredit}" )
                ->line( "Deposits Received: {$this->deposits}" )
                ->line( "Wallet Transactions: {$this->walletTransactions}" );
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'               => $this->title ,
                'message'             => $this->message ,
                'closed_by'           => $this->closedBy ,
                'closed_at'           => $this->closedAt ,
                'opening_float'       => $this->openingFloat ,
                'expected_float'      => $this->expectedFloat ,
                'closing_float'       => $this->closingFloat ,
                'discrepancy'         => $this->discrepancy ,
                'total_sales_value'   => $this->totalSalesValue ,
                'total_revenue'       => $this->totalRevenue ,
                'cost_of_goods'       => $this->totalCostOfGoods ,
                'gross_profit'        => $this->grossProfit ,
                'expenses'            => $this->expenses ,
                'net_profit'          => $this->netProfit ,
                'total_credit'        => $this->totalCredit ,
                'deposits'            => $this->deposits ,
                'wallet_transactions' => $this->walletTransactions ,
                'category'            => 'Finance' ,
                'icon'                => '🧾' ,
                'color'               => 'text-blue-500 bg-blue-50 dark:bg-blue-500/10' ,
            ];
        }
    }