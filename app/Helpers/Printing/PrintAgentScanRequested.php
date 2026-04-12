<?php

    namespace App\Helpers\Printing;

    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class PrintAgentScanRequested implements ShouldBroadcastNow
    {
        use Dispatchable , SerializesModels;

        public $businessId;

        public function __construct($businessId)
        {
            $this->businessId = $businessId;
        }

        public function broadcastOn() : PrivateChannel
        {
            return new PrivateChannel( 'business.' . $this->businessId );
        }

        public function broadcastAs()
        {
            return 'cloud-scan-printers';
        }
    }