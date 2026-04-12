<?php

    namespace App\Helpers\Printing;

    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class DrawerKickDispatched implements ShouldBroadcastNow
    {
        use Dispatchable , SerializesModels;

        public function __construct(
            public readonly string $tenantIdentifier ,
            public readonly string $printerName
        ) {}

        public function broadcastOn() : array
        {
            return [
                new PrivateChannel( 'business.' . $this->tenantIdentifier ) ,
            ];
        }

        public function broadcastAs() : string
        {
            return 'cloud-open-drawer';
        }

        public function broadcastWith() : array
        {
            return [
                'printerName' => $this->printerName ,
                'timestamp'   => now()->toIso8601String()
            ];
        }
    }