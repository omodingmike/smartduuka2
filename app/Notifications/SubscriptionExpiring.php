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
    class SubscriptionExpiring extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(public string $title , public string $message)
        {
            $this->afterCommit();
        }

        public function via(object $notifiable) : array
        {
//            $settings = Settings::group( 'notification' )->all();
            $settings = Settings::group( 'notification' )->get('events');

            $channels = [];

            // Decode the events JSON from settings
            $events = isset( $settings[ 'events' ] ) ? json_decode( $settings[ 'events' ] , TRUE ) : [];

            // Find the sub_expiring event
            $eventConfig = collect( $events )->firstWhere( 'id' , 'sub_expiring' );

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
            return ( new MailMessage )
                ->line( 'The introduction to the notification.' )
                ->action( 'Notification Action' , url( '/' ) )
                ->line( 'Thank you for using our application!' );
        }

        public function toArray($notifiable)
        {
            return [
                'title'    => $this->title ,
                'message'  => $this->message ,
                'category' => 'Frontend' ,
                'icon'     => '🎉' ,
                'color'    => 'text-green-500 bg-green-50 dark:bg-green-500/10' ,
            ];
        }
    }
