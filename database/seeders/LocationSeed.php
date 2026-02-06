<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocationSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prevent timeout for large files
        set_time_limit( 0 );
        ini_set( 'memory_limit' , '-1' );

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
            $path = database_path( "sql/{$filename}" );
            $this->importSqlStream( $path , $filename );
        }
    }
    private function cleanDatabase() : void
    {
        $tables = [ 'cities' , 'states' , 'countries' , 'subregions' , 'regions' ];

        // We use DROP ... CASCADE to force remove the tables and their links
        // The SQL files contain CREATE TABLE statements, so they will be rebuilt.
        $tableList = implode( ', ' , $tables );

        try {
            DB::statement( "DROP TABLE IF EXISTS {$tableList} CASCADE" );
            $this->command->info( "Dropped tables: {$tableList}" );
        } catch ( \Exception $e ) {
            $this->command->warn( 'Cleanup warning: ' . $e->getMessage() );
        }
    }

    private function importSqlStream(string $filePath , string $fileName) : void
    {
        if ( ! File::exists( $filePath ) ) {
            $this->command->error( "File missing: {$fileName}" );
            return;
        }

        $this->command->line( "Processing: {$fileName}..." );

        $handle = fopen( $filePath , 'r' );

        if ( ! $handle ) {
            $this->command->error( "Could not open file: {$fileName}" );
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
            $this->command->info( "Imported: {$fileName}" );

        } catch ( \Exception $e ) {
            DB::rollBack();
            $this->command->error( "Error importing {$fileName}: " . $e->getMessage() );
            // Close handle if error occurs
            fclose( $handle );
            return;
        }

        fclose( $handle );
    }
}
