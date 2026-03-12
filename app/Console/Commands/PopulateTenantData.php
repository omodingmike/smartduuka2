<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class PopulateTenantData extends Command
    {
        protected $signature   = 'tenants:populate-data';
        protected $description = 'Populate business_id and print_agent_token for existing tenants';

        /**
         * @throws TenantCouldNotBeIdentifiedById
         */
        public function handle()
        {
            $tenants = Tenant::whereNull( 'business_id' )->get();

            foreach ( $tenants as $tenant ) {
                $this->info( "Processing tenant: {$tenant->id}" );

//                tenancy()->initialize( $tenant );

                // Update the tenant with the new data
//                $tenant->update( [
//                    'business_id'       => Str::uuid()->getHex() ,
//                    'print_agent_token' => Str::random( 32 ) ,
//                ] );

                info($tenant);

                $this->info( "Tenant {$tenant->id} updated successfully." );
            }

            $this->info( 'All tenants have been updated.' );
            return 0;
        }
    }
