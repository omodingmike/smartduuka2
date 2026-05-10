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
    class UnusualLoginAttempt extends Notification implements ShouldQueue
    {
        use Queueable;

        public function __construct(
            public string $title ,
            public string $message ,
            public string $ip ,
            public string $device ,
            public string $location = 'Unknown' ,
        )
        {
            $this->afterCommit();
        }

        public function via(object $notifiable) : array
        {
            $settings = Settings::group( 'notification' )->all();
            $channels = [];

            $events      = $settings[ 'events' ] ?? [];
            $eventConfig = collect( $events )->firstWhere( 'id' , 'login_alert' );

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
            return ( new MailMessage )
                ->subject( $this->title )
                ->greeting( 'Security Alert!' )
                ->line( $this->message )
                ->line( "**IP Address:** {$this->ip}" )
                ->line( "**Device:** {$this->device}" )
                ->line( "**Location:** {$this->location}" )
                ->line( 'If this was you, no action is needed. If not, please secure your account immediately.' )
                ->action( 'Secure My Account' , url( '/settings/security' ) );
        }

        public function toArray($notifiable) : array
        {
            return [
                'title'    => $this->title ,
                'message'  => $this->message ,
                'ip'       => $this->ip ,
                'device'   => $this->device ,
                'location' => $this->location ,
                'category' => 'System' ,
                'icon'     => '🔐' ,
                'color'    => 'text-red-500 bg-red-50 dark:bg-red-500/10' ,
            ];
        }
    }