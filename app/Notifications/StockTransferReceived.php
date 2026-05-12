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
    class StockTransferReceived extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $transferNo ,
            public string $fromBranch ,
            public string $toBranch ,
            public string $requestDate ,
            public string $receivedDate ,
            public int $itemCount ,
            public string $receivedBy ,
            public string $frontend_url
        )
        {
            $this->afterCommit();
        }

        public function via(object $notifiable) : array
        {
            return notificationChannels( $notifiable , 'transfer_received' );
        }

        public function toMail(object $notifiable) : MailMessage
        {
            return ( new MailMessage )
                ->subject( $this->title )
                ->view( 'notifications.stocktransferrecieved' , [
                    'username'           => $notifiable->name ?? 'Client' ,
                    'frontend_url'       => $this->frontend_url ,
                    'source_branch'      => $this->fromBranch ,
                    'destination_branch' => $this->toBranch ,
                    'transfer_ref'       => $this->transferNo ,
                    'transfer_link'      => "$this->frontend_url" . '/stock' ,
                ] );
        }

        public function toArray(object $notifiable) : array
        {
            return [
                'title'         => $this->title ,
                'frontend_url'  => $this->frontend_url ,
                'message'       => $this->message ,
                'transfer_no'   => $this->transferNo ,
                'from_branch'   => $this->fromBranch ,
                'to_branch'     => $this->toBranch ,
                'request_date'  => $this->requestDate ,
                'received_date' => $this->receivedDate ,
                'received_by'   => $this->receivedBy ,
                'item_count'    => $this->itemCount ,
                'category'      => 'Stock' ,
                'icon'          => '🚚' ,
                'color'         => 'text-green-500 bg-green-50 dark:bg-green-500/10' ,
            ];
        }
    }
