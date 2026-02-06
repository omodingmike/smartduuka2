<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;


    class CreateTenant extends Command
    {
        protected $signature = 'app:create-tenant';

        protected $description = 'Command description';

        public function handle() : void
        {
            $tenant1 = Tenant::create( [ 'id' => 'demo' ] );
            $tenant1->domains()->create( [ 'domain' => 'demo-api.smartduuka.com' ] );

            $tenant1 = Tenant::create( [ 'id' => 'zakayo' ] );
            $tenant1->domains()->create( [ 'domain' => 'zakayo-api.smartduuka.com' ] );
        }
    }
