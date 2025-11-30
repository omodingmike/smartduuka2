<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class LocationSeed extends Command
    {
        protected $signature   = 'l:seed';
        protected $description = 'Seed locations tables';

        public function handle(): int
        {
            // Only importing cities here
            $this->importCities(database_path('locations/countries.csv'));
            $this->importCities(database_path('locations/states.csv'));
            $this->importCities(database_path('locations/cities.csv'));

            return self::SUCCESS;
        }

        private function importCities(string $filePath): void
        {
            if (!file_exists($filePath)) {
                $this->error("File not found: $filePath");
                return;
            }

            if (!($handle = fopen($filePath, 'r'))) {
                $this->error("Cannot open file: $filePath");
                return;
            }

            $header = fgetcsv($handle); // Read CSV header
            if (!$header || !is_array($header)) {
                $this->error("Invalid CSV header in $filePath");
                return;
            }

            $batch = [];
            $chunkSize = 1000;
            $rowNum = 1;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                if (!$row || count($row) == 0) {
                    continue; // skip empty rows
                }

                $data = array_combine($header, $row);

                // Check if state exists
                $state = DB::table('states')->where('id', $data['state_id'])->first();
                if (!$state) {
                    $skipped++;
                    continue; // skip city with invalid state
                }

                $batch[] = [
                    'name'       => $data['name'],
                    'state_id'   => $state->id,
                    'status'     => $data['status'] ?? 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ];

                if (count($batch) >= $chunkSize) {
                    DB::table('cities')->insert($batch);
                    $batch = [];
                }
            }

            // Insert remaining rows
            if (!empty($batch)) {
                DB::table('cities')->insert($batch);
            }

            fclose($handle);

            $this->info("Cities imported successfully. Skipped $skipped cities with missing states.");
        }
    }
