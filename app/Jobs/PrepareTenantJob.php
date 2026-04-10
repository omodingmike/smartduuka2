<?php

    namespace App\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Artisan;
    use Stancl\Tenancy\Contracts\TenantWithDatabase;

    class PrepareTenantJob implements ShouldQueue
    {
        use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

        public function __construct(public TenantWithDatabase $tenant) {}

        public function handle() : void
        {
            Artisan::call( 'tenants:migrate' , [
                '--tenants' => [ $this->tenant->getTenantKey() ] ,
            ] );
        }
    }
