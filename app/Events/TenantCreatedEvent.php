<?php

    namespace App\Events;

    use App\Models\User;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;
    use Stancl\Tenancy\Contracts\Tenant;

    class TenantCreatedEvent implements ShouldBroadcast
    {
        use Dispatchable , InteractsWithSockets , SerializesModels;

        public function __construct(public Tenant $tenant , public User $user) {}

        public function broadcastOn() : array
        {
            return [
                new PrivateChannel( "channel.{$this->user->id}" ) ,
            ];
        }
    }
