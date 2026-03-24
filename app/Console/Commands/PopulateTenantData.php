<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Str;

    class PopulateTenantData extends Command
    {
        protected $signature   = 'tenants:populate-data';
        protected $description = 'Populate tenant data.';

        public function handle() : void
        {
            Tenant::all()->runForEach( function (Tenant $tenant) {
                $tenant->update(
                    [
                        'business_id'  => time() + rand( 1 , 1000000000 ) ,
                        'pin_pepper'   => Str::uuid()->getHex() ,
                        'frontend_url' => tenant( 'id' ) . config( 'session.domain' )
                    ] );
            } );
        }
    }
