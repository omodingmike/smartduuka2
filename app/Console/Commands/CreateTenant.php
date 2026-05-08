<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Str;
    use Symfony\Component\Console\Command\Command as CommandAlias;

    class CreateTenant extends Command
    {
        protected $signature = 'create-tenant {id}';

        protected $description = 'Create a new tenant with the given ID. Domain will be {id}.smartduuka2.test';

        public function handle() : int
        {
            $id = $this->argument( 'id' );

            if ( Tenant::where( 'id' , $id )->exists() ) {
                $this->error( "A tenant with the ID '{$id}' already exists. Aborting." );
                return CommandAlias::FAILURE;
            }

            $root_domain = config( 'session.domain' );
            $domain      = "$id-api$root_domain";

            $this->info( "Creating tenant {$id} with domain {$domain}..." );

            $tenant = Tenant::create( [
                'id'           => $id ,
                'business_id'  => time() + rand( 1 , 1000000000 ) ,
                'pin_pepper'   => Str::uuid()->getHex() ,
                'frontend_url' => $id . config( 'session.domain' )
            ] );

            $tenant->domains()->create( [ 'domain' => $domain ] );

            $this->info( "Tenant {$id} created successfully." );

            return CommandAlias::SUCCESS;
        }
    }