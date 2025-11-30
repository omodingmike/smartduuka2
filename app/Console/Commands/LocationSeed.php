<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class LocationSeed extends Command
    {
        protected $signature   = 'l:seed';
        protected $description = 'Seed countries, states, and cities';

        public function handle() : int
        {
            $this->importCountries( database_path( 'locations/countries.csv' ) );
            $this->importStates( database_path( 'locations/states.csv' ) );
            $this->importCities( database_path( 'locations/cities.csv' ) );

            return self::SUCCESS;
        }

        /**
         * Import Countries
         */
        private function importCountries(string $filePath) : void
        {
            $this->info( 'Importing countries...' );
            $this->importCSV( $filePath , 'countries' , function ($data) {
                return [
                    'id'         => $data[ 'id' ] ,
                    'name'       => $data[ 'name' ] ,
                    'code'       => $data[ 'code' ] ?? NULL ,
                    'status'     => $data[ 'status' ] ?? 5 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'deleted_at' => NULL ,
                ];
            } );
        }

        /**
         * Import States
         */
        private function importStates(string $filePath) : void
        {
            $this->info( 'Importing states...' );
            $this->importCSV( $filePath , 'states' , function ($data) {
                // Validate country
                $country = DB::table( 'countries' )->where( 'id' , $data[ 'country_id' ] )->first();
                if ( ! $country ) {
                    return NULL; // Skip state if country missing
                }

                return [
                    'id'         => $data[ 'id' ] ,
                    'name'       => $data[ 'name' ] ,
                    'country_id' => $country->id ,
                    'status'     => $data[ 'status' ] ?? 5 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'deleted_at' => NULL ,
                ];
            } );
        }

        /**
         * Import Cities
         */
        private function importCities(string $filePath) : void
        {
            $this->info( 'Importing cities...' );
            $this->importCSV( $filePath , 'cities' , function ($data) {

                // Validate state
                $state = DB::table( 'states' )->where( 'id' , $data[ 'state_id' ] )->first();
                if ( ! $state ) {
                    return NULL; // Skip city if state missing
                }

                return [
                    'id'         => $data[ 'id' ] ?? NULL ,
                    'name'       => $data[ 'name' ] ,
                    'state_id'   => $state->id ,
                    'status'     => $data[ 'status' ] ?? 5 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'deleted_at' => NULL ,
                ];
            } );
        }

        /**
         * Generic CSV importer
         */
        private function importCSV(string $filePath , string $table , callable $mapFunction) : void
        {
            if ( ! file_exists( $filePath ) ) {
                $this->error( "File missing: $filePath" );
                return;
            }

            if ( ! ( $handle = fopen( $filePath , 'r' ) ) ) {
                $this->error( "Cannot open: $filePath" );
                return;
            }

            $header = fgetcsv( $handle );
            if ( ! $header ) {
                $this->error( "Invalid CSV header: $filePath" );
                return;
            }

            $batch     = [];
            $chunkSize = 1000;
            $skipped   = 0;

            while ( ( $row = fgetcsv( $handle ) ) !== FALSE ) {
                $data = array_combine( $header , $row );

                $mapped = $mapFunction( $data );

                if ( ! $mapped ) {
                    $skipped++;
                    continue;
                }

                $batch[] = $mapped;

                if ( count( $batch ) >= $chunkSize ) {
                    DB::table( $table )->insert( $batch );
                    $batch = [];
                }
            }

            if ( ! empty( $batch ) ) {
                DB::table( $table )->insert( $batch );
            }

            fclose( $handle );

            $this->info( "Imported into [$table], skipped: $skipped" );
        }
    }
