<?php

    namespace App\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Stancl\Tenancy\Contracts\TenantWithDatabase;

    class PrepareTenantJob implements ShouldQueue
    {
        use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

        public function __construct(public TenantWithDatabase $tenant) {}

        public function handle() : void {}
    }
