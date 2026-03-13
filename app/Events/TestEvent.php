<?php

    namespace App\Events;

    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class TestEvent implements ShouldBroadcastNow
    {
        use Dispatchable , InteractsWithSockets , SerializesModels;

        public $message;
        public $time;
        public $businessId; // Added to store the tenant identifier

        /**
         * Create a new event instance.
         * We now require the businessId to target the correct private channel.
         */
        public function __construct($businessId , $message = 'Hello from Reverb!')
        {
            $this->businessId = $businessId;
            $this->message    = $message;
            $this->time       = now()->toDateTimeString();
        }

        /**
         * Get the channels the event should broadcast on.
         * Changed from public Channel to PrivateChannel with the business identifier.
         */
        public function broadcastOn() : array
        {
            // This must match the pattern in channels.php: 'business.{identifier}'
            return [
                new PrivateChannel( 'business.' . $this->businessId ) ,
            ];
        }

        /**
         * The event's broadcast name.
         */
        public function broadcastAs() : string
        {
            // This matches '.TestEvent' in your Next.js page.tsx
            return 'TestEvent';
        }
    }