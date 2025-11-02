<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    class LocationSeed extends Command
    {
        protected $signature = 'l:seed';

        protected $description = 'Command description';

        public function handle()
        {
            $this->sqlImport('countries' , 'database/locations/countries.sql');
            $this->sqlImport('states' , 'database/locations/states.sql');
            $this->sqlImport('cities' , 'database/locations/cities.sql');
            return 1;
        }

        private function sqlImport(string $table , string $file_path)
        {
            if ( ! Schema::hasTable($table) ) {
                $path = base_path($file_path);
                if ( file_exists($path) ) {
                    DB::unprepared(file_get_contents($path));
                    $this->info("$table imported successfully.");
                } else {
                    $this->error("SQL file not found: $path");
                }
            } else {
                $this->error("$table already exists.");
            }
        }
    }
