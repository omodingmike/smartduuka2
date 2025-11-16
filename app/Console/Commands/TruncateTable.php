<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTable extends Command
{
    protected $signature = 'table:truncate {table}';
    protected $description = 'Truncate a database table safely across supported drivers';

    public function handle(): int
    {
        $table = $this->argument('table');
        $driver = DB::getDriverName();

        try {
            switch ($driver) {
                case 'mysql':
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::table($table)->truncate();
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    break;

                case 'pgsql':
                    // PostgreSQL syntax — reset identity and cascade to related tables
                    DB::statement('TRUNCATE TABLE "' . $table . '" RESTART IDENTITY CASCADE;');
                    break;

                case 'sqlite':
                    // SQLite syntax — delete all rows and reset sequence
                    DB::statement('DELETE FROM "' . $table . '";');
                    DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}';");
                    break;

                default:
                    DB::table($table)->truncate();
                    break;
            }

            $this->info("✅ Truncated table: {$table}");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Failed to truncate table {$table}: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
