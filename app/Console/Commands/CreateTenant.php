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
            $tenant1 = Tenant::create( [ 'id' => 'api2' ] );
            $tenant1->domains()->create( [ 'domain' => 'api2.smartduuka2.test' ] );
        }
    }
