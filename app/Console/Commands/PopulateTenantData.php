<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;

    class PopulateTenantData extends Command
    {
        protected $signature   = 'tenants:populate-data';
        protected $description = 'Populate business_id and print_agent_token for existing tenants';

        public function handle() : int
        {
            $tenants = Tenant::all();

            foreach ( $tenants as $tenant ) {
                $businessId = $tenant->business_id;
                if ( ! $businessId ) {
                    $tenant->update( [ 'business_id' => time() + rand( 10 , 100000000 ) ] );
                }
            }
            return 0;
        }
    }
