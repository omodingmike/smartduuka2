<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Attributes\Description;
    use Illuminate\Console\Attributes\Signature;
    use Illuminate\Console\Command;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    #[Signature( 'add-branch-id-to-all-central-tables' )]
    #[Description( 'Add a branch_id column to all tenant tables' )]
    class AddBranchIdToAllCentralTables extends Command
    {
        public function handle() : void
        {
            $excludedTables = [
                'branches' ,
                'migrations' ,
                'failed_jobs' ,
                'password_resets' ,
                'password_reset_tokens' ,
                'personal_access_tokens' ,
                'sessions' ,
                'cache' ,
                'cache_locks' ,
                'jobs' ,
                'job_batches'
            ];

            // Fetch tables exclusively for PostgreSQL
            $tables = array_map(
                fn($table) => $table->tablename ,
                DB::select( "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'" )
            );

            $this->info( 'Found ' . count( $tables ) . ' tables in the database.' );
            $this->newLine();

            foreach ( $tables as $table ) {
                if ( in_array( $table , $excludedTables ) ) {
                    $this->line( "<fg=yellow>Skipping excluded table:</> {$table}" );
                    continue;
                }

                if ( ! Schema::hasColumn( $table , 'branch_id' ) ) {
                    Schema::table( $table , function (Blueprint $tableBlueprint) {
                        $tableBlueprint->unsignedBigInteger( 'branch_id' )->default( 1 );
                    } );

                    $this->info( "✔ Added branch_id to: {$table}" );
                }
                else {
                    $this->line( "<fg=cyan>Column already exists in:</> {$table}" );
                }
            }

            $this->newLine();
            $this->info( 'Process completed successfully.' );
        }
    }