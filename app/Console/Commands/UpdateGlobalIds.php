<?php

    namespace App\Console\Commands;

    use App\Enums\Role;
    use App\Models\CentralUser;
    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Console\Command;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class UpdateGlobalIds extends Command
    {
        protected $signature = 'update-global-ids';

        protected $description = 'Synchronize global_ids between central and tenant databases';

        public function handle() : void
        {
            try {
                $this->info( 'Step 1: Ensuring all Central Users have a global_id...' );

                foreach ( CentralUser::all() as $centralUser ) {
                    if ( ! $centralUser->global_id ) {
                        $centralUser->update( [ 'global_id' => Str::uuid() ] );
                    }
                }

                $this->info( 'Step 2: Syncing Tenant Users to Central Users...' );
                $tenants = Tenant::all();

                foreach ( $tenants as $tenant ) {
                    $this->line( "Processing Tenant: {$tenant->id}" );
                    tenancy()->initialize( $tenant );

                    foreach ( User::all() as $user ) {
                        if ( $user->hasRole( Role::CUSTOMER ) ) continue;

                        $centralUser = CentralUser::where( 'email' , $user->email )->first();

                        if ( $centralUser ) {
                            if ( $user->global_id !== $centralUser->global_id ) {
                                $user->withoutEvents( function () use ($user , $centralUser) {
                                    $user->forceFill( [ 'global_id' => $centralUser->global_id ] )->save();
                                } );
                                $this->info( "   -> Synced ID for: {$user->email}" );
                            }

                            $centralUser->tenants()->syncWithoutDetaching( [ $tenant->id ] );
                        }
                        else {
                            $this->warn( "   -> No Central User found for email: {$user->email}" );
                        }
                    }
                    tenancy()->end();
                }

                $this->info( 'Synchronization complete!' );

            } catch ( TenantCouldNotBeIdentifiedById $e ) {
                $this->error( $e->getMessage() );
                info( $e->getMessage() );
            }
        }
    }