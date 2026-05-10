<?php

    namespace App\Notifications;

    use Illuminate\Bus\Queueable;
    use Illuminate\Notifications\Notification;

    class FrontendNotification extends Notification
    {
        use Queueable;

        private $title;
        private $message;

        /**
         * Create a new notification instance.
         *
         * @return void
         */
        public function __construct($title , $message)
        {
            $this->title   = $title;
            $this->message = $message;
        }

        /**
         * Get the notification's delivery channels.
         *
         * @param mixed $notifiable
         *
         * @return array
         */
        public function via($notifiable)
        {
            return [ 'database' ];
        }

        /**
         * Get the array representation of the notification.
         *
         * @param mixed $notifiable
         *
         * @return array
         */
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
