<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;


    class CreateTenant extends Command
    {
        protected $signature = 'app:create-tenant {id}';

        protected $description = 'Create a new tenant with the given ID. Domain will be {id}.smartduuka2.test';

        public function handle() : void
        {
            $id          = $this->argument( 'id' );
            $root_domain = config( 'session.domain' );
            $domain      = "$id-api$root_domain";

            $this->info( "Creating tenant {$id} with domain {$domain}..." );

            $tenant = Tenant::create( [ 'id' => $id ] );
            $tenant->domains()->create( [ 'domain' => $domain ] );

            $this->info( "Tenant {$id} created successfully." );
        }
    }
