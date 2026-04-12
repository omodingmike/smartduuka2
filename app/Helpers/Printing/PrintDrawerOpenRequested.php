<?php

    namespace App\Helpers\Printing;

    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class PrintDrawerOpenRequested implements ShouldBroadcastNow
    {
        use Dispatchable , SerializesModels;

        public $businessId;
        public $printerName;

        public function __construct($businessId , $printerName)
        {
            $this->businessId  = $businessId;
            $this->printerName = $printerName;
        }

        public function broadcastOn()
        {
            return new PrivateChannel( 'business.' . $this->businessId );
        }

        public function broadcastAs()
        {
            return 'cloud-open-drawer';
        }

        public function broadcastWith()
        {
            return [ 'printerName' => $this->printerName ];
        }
    }