<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Schema;
    use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

    class LocationSeed extends Seeder
    {
        public function run(): void
        {
            $this->ensureCentralContext();

            set_time_limit(0);
            ini_set('memory_limit', '-1');

            if ($this->isAlreadySeeded()) {
                $this->logInfo('Location tables already seeded. Skipping...');
                return;
            }

            $this->cleanDatabase();

            foreach ($this->sqlFiles() as $filename) {
                $path = database_path("sql/{$filename}");
                $this->importSqlStream($path, $filename);
            }
        }

        /**
         * Abort if this seeder is somehow invoked inside a tenant context.
         * stancl/tenancy switches the default DB connection when a tenant is
         * initialised, so we guard against accidentally writing location data
         * into a tenant DB.
         */
        private function ensureCentralContext(): void
        {
            // tenancy()->initialized is true when a tenant context is active
            if (app()->bound('tenancy') && tenancy()->initialized) {
                throw new \RuntimeException(
                    'LocationSeed must run in the central database context. ' .
                    'Call `php artisan db:seed --class=LocationSeed` without ' .
                    'initialising a tenant first.'
                );
            }
        }

        private function isAlreadySeeded(): bool
        {
            return Schema::hasTable('countries') && DB::table('countries')->exists();
        }

        private function sqlFiles(): array
        {
            // Strict parent-first order — foreign keys depend on this
            return [
                'regions.sql',
                'subregions.sql',
                'countries.sql',
                'states.sql',
                'cities.sql',
            ];
        }

        private function cleanDatabase(): void
        {
            // Child tables first to respect FK constraints
            $tables = ['cities', 'states', 'countries', 'subregions', 'regions'];
            $tableList = implode(', ', $tables);

            try {
                DB::statement("DROP TABLE IF EXISTS {$tableList} CASCADE");
                $this->logInfo("Dropped tables: {$tableList}");
            } catch (\Exception $e) {
                $this->logWarn('Cleanup warning: ' . $e->getMessage());
            }
        }

        private function importSqlStream(string $filePath, string $fileName): void
        {
            if (!File::exists($filePath)) {
                $this->logError("File missing: {$fileName}");
                return;
            }

            $handle = fopen($filePath, 'r');

            if (!$handle) {
                $this->logError("Could not open file: {$fileName}");
                return;
            }

            $this->logInfo("Processing: {$fileName}...");

            DB::beginTransaction();

            try {
                $buffer = '';

                while (($line = fgets($handle)) !== false) {
                    $trimmed = trim($line);

                    if ($this->shouldSkipLine($trimmed, $line)) {
                        continue;
                    }

                    $buffer .= $line;

                    if (str_ends_with($trimmed, ';')) {
                        DB::unprepared($buffer);
                        $buffer = '';
                    }
                }

                DB::commit();
                $this->logInfo("Imported: {$fileName}");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logError("Error importing {$fileName}: " . $e->getMessage());
            } finally {
                fclose($handle);
            }
        }

        private function shouldSkipLine(string $trimmed, string $raw): bool
        {
            // Empty lines and SQL comments
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '\\')) {
                return true;
            }

            // PostgreSQL role/ownership directives (not portable)
            if (stripos($raw, 'OWNER TO') !== false) {
                return true;
            }

            // Session-level SET commands (client_encoding, lock_timeout, etc.)
            if (str_starts_with($trimmed, 'SET ')) {
                return true;
            }

            // DROP statements — cleanDatabase() already handled teardown
            if (stripos($raw, 'DROP TABLE') !== false || stripos($raw, 'DROP CONSTRAINT') !== false) {
                return true;
            }

            return false;
        }

        // ─── Logging ──────────────────────────────────────────────────────────────

        private function logInfo(string $message): void
        {
            isset($this->command) ? $this->command->info($message) : Log::info($message);
        }

        private function logWarn(string $message): void
        {
            isset($this->command) ? $this->command->warn($message) : Log::warning($message);
        }

        private function logError(string $message): void
        {
            isset($this->command) ? $this->command->error($message) : Log::error($message);
        }
    }