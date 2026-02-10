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
            $tenant1 = Tenant::create( [ 'id' => 'app' ] );
            $tenant1->domains()->create( [ 'domain' => 'app.smartduuka2.test' ] );

//            $tenant2 = Tenant::create( [ 'id' => 'zakayo' ] );
//            $tenant2->domains()->create( [ 'domain' => 'zakayo-api.smartduuka.com' ] );
        }
    }
