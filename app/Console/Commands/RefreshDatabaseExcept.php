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
         * * --tables: Comma-separated list of tables to KEEP (not drop).
         * --tenant: The ID of the tenant to target (optional).
         */
        protected $signature = 'db:refresh-except 
                            {--tables=users,settings : Comma-separated list of tables to exclude from dropping} 
                            {--tenant= : The tenant ID to run the command for (optional)}';

        /**
         * The console command description.
         */
        protected $description = 'Drops all tables except specified ones and re-migrates. Supports Laravel 12, PostgreSQL CASCADE, and Stancl Tenancy.';

        /**
         * Execute the console command.
         */
        public function handle()
        {
            // 1. Environment Safety Check
            if ( ! app()->environment( 'local' , 'testing' ) ) {
                if ( ! $this->confirm( 'CAUTION: You are running this in a non-local environment. Do you wish to continue?' ) ) {
                    return;
                }
            }

            $tenantId       = $this->option( 'tenant' );
            $excludedTables = explode( ',' , $this->option( 'tables' ) );

            // Always preserve the migrations table so the system knows what to re-run
            $excludedTables[] = 'migrations';

            // 2. Initialize Tenancy Context
            if ( $tenantId ) {
                $tenantModel = config( 'tenancy.tenant_model' , \App\Models\Tenant::class );
                $tenant      = $tenantModel::find( $tenantId );

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

            // 3. Identify Tables (Laravel 12 Native Schema)
            $tables        = Schema::getTables();
            $allTableNames = array_map( fn($table) => $table[ 'name' ] , $tables );

            $tablesToDrop = array_diff( $allTableNames , $excludedTables );

            if ( empty( $tablesToDrop ) ) {
                $this->info( 'No tables found to drop based on your exclusions.' );
                return;
            }

            // 4. Drop and Clean Migration History
            // We disable constraints, but PostgreSQL still requires CASCADE for physical drops
            Schema::disableForeignKeyConstraints();

            foreach ( $tablesToDrop as $table ) {
                // Using DB::statement with CASCADE to bypass "Dependent objects still exist" errors in Postgres
                DB::statement( "DROP TABLE \"{$table}\" CASCADE" );

                // Clean up the migration history so Laravel thinks the table was never migrated
                $pattern         = "%_create_{$table}_table";
                $singularPattern = '%_create_' . Str::singular( $table ) . '_table';
                $pluralPattern   = '%_create_' . Str::plural( $table ) . '_table';

                DB::table( 'migrations' )
                  ->where( 'migration' , 'like' , $pattern )
                  ->orWhere( 'migration' , 'like' , $singularPattern )
                  ->orWhere( 'migration' , 'like' , $pluralPattern )
                  ->delete();

                $this->line( "<fg=yellow>Dropped (Cascaded) & Cleaned migration for:</> $table" );
            }

            Schema::enableForeignKeyConstraints();

            // 5. Re-run Migrations
            $this->info( 'Re-running migrations...' );
            if ( $tenantId ) {
                // Re-migrates only the current tenant's database
                $this->call( 'tenancy:migrate' , [ '--tenants' => [ $tenantId ] ] );
            }
            else {
                // Re-migrates the central database
                $this->call( 'migrate' );
            }

            $this->info( 'Database refresh complete!' );
            $this->info( 'Preserved tables: ' . implode( ', ' , array_filter( $excludedTables , fn($t) => $t !== 'migrations' ) ) );
        }
    }