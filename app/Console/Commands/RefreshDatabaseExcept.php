<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RefreshDatabaseExcept extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-except {--tables=users,settings : Comma-separated list of tables to exclude}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database tables except specified ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!app()->environment('local', 'testing')) {
            if (!$this->confirm('You are running this command in a non-local environment. Do you wish to continue?')) {
                return;
            }
        }

        $excludedTables = explode(',', $this->option('tables'));
        // Always exclude migrations table
        $excludedTables[] = 'migrations';
        
        $allTables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        
        // Filter out excluded tables
        $tablesToDrop = array_diff($allTables, $excludedTables);

        if (empty($tablesToDrop)) {
            $this->info('No tables to drop.');
            return;
        }

        $this->info('Dropping tables: ' . implode(', ', $tablesToDrop));

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        foreach ($tablesToDrop as $table) {
            Schema::drop($table);
            $this->line("Dropped table: $table");
            
            // Attempt to remove migration entry
            // Matches: create_tablename_table, create_table_name_table
            // We try to be smart about singular/plural
            $singular = Str::singular($table);
            $plural = Str::plural($table);
            
            DB::table('migrations')->where(function($query) use ($table, $singular, $plural) {
                $query->where('migration', 'like', "%_create_{$table}_table")
                      ->orWhere('migration', 'like', "%_create_{$singular}_table")
                      ->orWhere('migration', 'like', "%_create_{$plural}_table");
            })->delete();
            
            $this->line("Removed migration entry for: $table");
        }

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->info('Tables dropped and migration entries cleaned.');

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Database refreshed (except specified tables).');
    }
}
