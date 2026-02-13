<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Facades\Tenancy;

    class RefreshDatabaseExcept extends Command
    {
        /**
         * The name and signature of the console command.
         * --tables: Specify what to KEEP.
         * --tenant: Specify which tenant to target.
         */
        protected $signature = 'db:refresh-except 
                            {--tables=users,settings : Comma-separated list of tables to exclude from dropping} 
                            {--tenant= : The tenant ID to run the command for}';

        protected $description = 'Drops all tables except specified ones and re-migrates (Supports Laravel 12 & Tenancy)';

        public function handle()
        {
            // 1. Production Guard
            if ( ! app()->environment( 'local' , 'testing' ) ) {
                if ( ! $this->confirm( 'CAUTION: You are running this in production. Do you wish to continue?' ) ) {
                    return;
                }
            }

            $tenantId         = $this->option( 'tenant' );
            $excludedTables   = explode( ',' , $this->option( 'tables' ) );
            $excludedTables[] = 'migrations'; // Critical: Never drop the migrations table

            // 2. Initialize Tenancy if requested
            if ( $tenantId ) {
                $tenant = config( 'tenancy.tenant_model' , \App\Models\Tenant::class )::find( $tenantId );
                if ( ! $tenant ) {
                    $this->error( "Tenant '$tenantId' not found." );
                    return;
                }
                Tenancy::initialize( $tenant );
                $this->info( 'Targeting Tenant Database: ' . $tenant->getTenantKey() );
            }
            else {
                $this->info( 'Targeting Central Database' );
            }

            // 3. Get Tables (Laravel 12 Native Method)
            // Schema::getTables() returns an array of arrays. Each has a 'name' key.
            $tables        = Schema::getTables();
            $allTableNames = array_map( fn($table) => $table[ 'name' ] , $tables );

            $tablesToDrop = array_diff( $allTableNames , $excludedTables );

            if ( empty( $tablesToDrop ) ) {
                $this->info( 'No tables found to drop based on your exclusions.' );
                return;
            }

            // 4. Drop and Clean Migration History
            Schema::disableForeignKeyConstraints();

            foreach ( $tablesToDrop as $table ) {
                Schema::drop( $table );

                // We must delete the migration record so Laravel re-runs the "create" migration
                $pattern         = "%_create_{$table}_table";
                $singularPattern = '%_create_' . Str::singular( $table ) . '_table';

                DB::table( 'migrations' )
                  ->where( 'migration' , 'like' , $pattern )
                  ->orWhere( 'migration' , 'like' , $singularPattern )
                  ->delete();

                $this->line( "<fg=yellow>Dropped & Cleaned migration for:</> $table" );
            }

            Schema::enableForeignKeyConstraints();

            // 5. Re-run Migrations
            $this->info( 'Re-running migrations...' );
            if ( $tenantId ) {
                $this->call( 'tenancy:migrate' , [ '--tenants' => [ $tenantId ] ] );
            }
            else {
                $this->call( 'migrate' );
            }

            $this->info( 'Database refresh complete!' );
        }
    }