<?php

    namespace App\Console\Commands;

    use App\Models\CentralUser;
    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Console\Command;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class SyncTenantUsersToCentral extends Command
    {
        protected $signature = 'sync-users';

        protected $description = 'Sync all tenant users to central database';

        public function handle() : void
        {
            try {
                $tenants = Tenant::all();

                if ( $tenants->isEmpty() ) {
                    $this->warn( 'No tenants found in the database.' );
                    return;
                }

                $this->info( "Found {$tenants->count()} tenants. Starting sync..." );

                foreach ( $tenants as $tenant ) {
                    $this->line( "--> Syncing users for tenant: {$tenant->id}" );

                    tenancy()->initialize( $tenant );
                    $tenantUsers = User::all()->toArray();
                    tenancy()->end();

                    $syncedCount = 0;

                    foreach ( $tenantUsers as $userData ) {
                        $globalId = $userData[ 'global_id' ] ?? (string) Str::uuid();

                        $payload                = Arr::except( $userData , [ 'id' , 'email' ] );
                        $payload[ 'global_id' ] = $globalId;
                        $payload[ 'tenant_id' ] = $tenant->id;

                        $centralUser = CentralUser::updateOrCreate(
                            [ 'email' => $userData[ 'email' ] ] ,
                            $payload
                        );

                        DB::table( 'tenant_users' )->updateOrInsert(
                            [
                                'tenant_id'      => $tenant->id ,
                                'global_user_id' => $centralUser->global_id ,
                            ]
                        );

                        if ( empty( $userData[ 'global_id' ] ) ) {
                            tenancy()->initialize( $tenant );
                            User::where( 'id' , $userData[ 'id' ] )->update( [ 'global_id' => $globalId ] );
                            tenancy()->end();
                        }

                        $syncedCount++;
                    }

                    $this->info( "    Synced {$syncedCount} users from {$tenant->id}." );
                }

                $this->newLine();
                $this->info( '✅ All tenant users successfully synced to the central database!' );

            } catch ( TenantCouldNotBeIdentifiedById $e ) {
                $this->info( $e->getMessage() );
            }
        }
    }