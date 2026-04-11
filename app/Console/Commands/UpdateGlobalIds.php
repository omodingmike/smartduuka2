<?php

    namespace App\Console\Commands;

    use App\Enums\Role;
    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Console\Command;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class UpdateGlobalIds extends Command
    {
        protected $signature = 'update-global-ids';

        protected $description = 'Command description';

        public function handle() : void
        {
            try {
                foreach ( User::all() as $user ) {
                    if ( ! $user->global_id ) $user->update( [ 'global_id' => Str::uuid() ] );
                }
                $tenants = Tenant::all();
                foreach ( $tenants as $tenant ) {
                    tenancy()->initialize( $tenant );
                    foreach ( User::all() as $user ) {
                        if ( ! $user->hasRole( Role::CUSTOMER ) ) $user->update( [ 'global_id' => Str::uuid() ] );
                    }
                    tenancy()->end();
                }
            } catch ( TenantCouldNotBeIdentifiedById $e ) {
                info( $e->getMessage() );
            }
        }
    }
