<?php

    namespace App\Helpers\printing;

    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class PrintJobDispatched implements ShouldBroadcastNow
    {
        use Dispatchable, SerializesModels;

        public function __construct(
            public readonly string $tenantIdentifier,
            public readonly array $payload
        ) {}

        public function broadcastOn(): array
        {
            return [
                new PrivateChannel('business.' . $this->tenantIdentifier),
            ];
        }

        public function broadcastAs(): string
        {
            return 'print-job';
        }

        public function broadcastWith(): array
        {
            return $this->payload;
        }
    }