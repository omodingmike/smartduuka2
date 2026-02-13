<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    class RefreshDatabaseExcept extends Command
    {
        protected $signature = 'db:refresh-except 
                            {--tables=users,settings : Comma-separated list of tables to exclude} 
                            {--tenant= : The tenant ID to run the command for (Stancl Tenancy)}';

        protected $description = 'Refresh database tables except specified ones, supporting Stancl Tenancy';

        public function handle()
        {
            // 1. Environment Safety Check
            if ( ! app()->environment( 'local' , 'testing' ) ) {
                if ( ! $this->confirm( 'You are running this in production. Continue?' ) ) {
                    return;
                }
            }

            $tenantId         = $this->option( 'tenant' );
            $excludedTables   = explode( ',' , $this->option( 'tables' ) );
            $excludedTables[] = 'migrations';

            // 2. Tenancy Initialization
            if ( $tenantId ) {
                $tenant = \App\Models\Tenant::find( $tenantId );
                if ( ! $tenant ) {
                    $this->error( "Tenant '$tenantId' not found." );
                    return;
                }
                // Switch database connection to the tenant
                tenancy()->initialize( $tenant );
                $this->info( "Scope: Tenant Database ($tenantId)" );
            }
            else {
                $this->info( 'Scope: Central Database' );
            }

            // 3. Identify and Drop Tables
            $allTables    = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
            $tablesToDrop = array_diff( $allTables , $excludedTables );

            if ( empty( $tablesToDrop ) ) {
                $this->info( 'No tables to drop.' );
                return;
            }

            Schema::disableForeignKeyConstraints();

            foreach ( $tablesToDrop as $table ) {
                Schema::drop( $table );

                // Cleanup migration history so Laravel thinks they need to be re-run
                $singular = Str::singular( $table );
                $plural   = Str::plural( $table );

                DB::table( 'migrations' )->where( function ($query) use ($table , $singular , $plural) {
                    $query->where( 'migration' , 'like' , "%_create_{$table}_table" )
                          ->orWhere( 'migration' , 'like' , "%_create_{$singular}_table" )
                          ->orWhere( 'migration' , 'like' , "%_create_{$plural}_table" );
                } )->delete();

                $this->line( "Cleaned: $table" );
            }

            Schema::enableForeignKeyConstraints();

            // 4. Re-run Migrations
            $this->info( 'Running migrations...' );
            if ( $tenantId ) {
                // stancl/tenancy specific command
                $this->call( 'tenancy:migrate' , [ '--tenants' => [ $tenantId ] ] );
            }
            else {
                $this->call( 'migrate' );
            }

            $this->info( 'Refresh complete.' );
        }
    }