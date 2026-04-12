<?php

    namespace App\Helpers\Printing;

    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class PrintJobDispatched implements ShouldBroadcastNow
    {
        use Dispatchable , SerializesModels;

        public $businessId;
        public $payload;

        public function __construct($businessId , $payload)
        {
            $this->businessId = $businessId;
            $this->payload    = $payload;
        }

        public function broadcastOn() : PrivateChannel
        {
            return new PrivateChannel( 'business.' . $this->businessId );
        }

        public function broadcastAs()
        {
            return 'print-job';
        }

        public function broadcastWith()
        {
            return $this->payload;
        }
    }