<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\File;

    class LocationSeed extends Command
    {
        protected $signature   = 'l:seed';
        protected $description = 'Import location data via streaming SQL';

        public function handle() : int
        {
            // Prevent timeout for large files
            set_time_limit( 0 );
            ini_set( 'memory_limit' , '-1' );

            $this->warn( 'Dropping old tables to reset structure...' );
            $this->cleanDatabase();

            // STRICT ORDER: Parents first, children last
            $files = [
                'regions.sql' ,
                'subregions.sql' ,
                'countries.sql' ,
                'states.sql' ,
                'cities.sql' ,
            ];

            foreach ( $files as $filename ) {
                $path = database_path( "locations/{$filename}" );
                $this->importSqlStream( $path , $filename );
            }

            $this->info( 'Location seeding completed successfully!' );
            return self::SUCCESS;
        }

        /**
         * USES DROP CASCADE instead of Truncate.
         * This destroys the tables so the SQL files can recreate them
         * without fighting over existing Foreign Key constraints.
         */
        private function cleanDatabase() : void
        {
            $tables = [ 'cities' , 'states' , 'countries' , 'subregions' , 'regions' ];

            // We use DROP ... CASCADE to force remove the tables and their links
            // The SQL files contain CREATE TABLE statements, so they will be rebuilt.
            $tableList = implode( ', ' , $tables );

            try {
                DB::statement( "DROP TABLE IF EXISTS {$tableList} CASCADE" );
                $this->info( "Dropped tables: {$tableList}" );
            } catch ( \Exception $e ) {
                $this->warn( 'Cleanup warning: ' . $e->getMessage() );
            }
        }

        /**
         * Reads SQL file line-by-line and filters out problematic commands.
         */
        private function importSqlStream(string $filePath , string $fileName) : void
        {
            if ( ! File::exists( $filePath ) ) {
                $this->error( "File missing: {$fileName}" );
                return;
            }

            $this->line( "Processing: {$fileName}..." );

            $handle = fopen( $filePath , 'r' );

            if ( ! $handle ) {
                $this->error( "Could not open file: {$fileName}" );
                return;
            }

            // Use transaction for speed
            DB::beginTransaction();

            try {
                $buffer = '';

                while ( ( $line = fgets( $handle ) ) !== FALSE ) {
                    $trimmed = trim( $line );

                    // --- FILTERS ---

                    // 1. Skip empty lines and standard comments
                    if ( $trimmed === '' || str_starts_with( $trimmed , '--' ) || str_starts_with( $trimmed , '\\' ) ) {
                        continue;
                    }

                    // 2. Skip "OWNER TO" (Fixes: role "postgres" does not exist)
                    if ( stripos( $line , 'OWNER TO' ) !== FALSE ) {
                        continue;
                    }

                    // 3. Skip "SET" commands (Optimization configs that might fail on your DB)
                    if ( str_starts_with( $trimmed , 'SET ' ) ) {
                        continue;
                    }

                    // 4. Skip "DROP" commands (We already dropped them in cleanDatabase)
                    // This prevents "cannot drop constraint" errors
                    if ( stripos( $line , 'DROP TABLE' ) !== FALSE || stripos( $line , 'DROP CONSTRAINT' ) !== FALSE ) {
                        continue;
                    }

                    // --- EXECUTION ---

                    $buffer .= $line;

                    // Execute when we hit a semicolon
                    if ( str_ends_with( $trimmed , ';' ) ) {
                        DB::unprepared( $buffer );
                        $buffer = '';
                    }
                }

                DB::commit();
                $this->info( "Imported: {$fileName}" );

            } catch ( \Exception $e ) {
                DB::rollBack();
                $this->error( "Error importing {$fileName}: " . $e->getMessage() );
                // Close handle if error occurs
                fclose( $handle );
                return;
            }

            fclose( $handle );
        }
    }