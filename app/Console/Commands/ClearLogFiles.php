<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearLogFiles extends Command
{
    protected $signature = 'logs:clear';

    protected $description = 'Truncate all log files in the storage/logs directory';

    public function handle(): void
    {
        $logPath = storage_path('logs');

        if (! is_dir($logPath)) {
            $this->warn("Log directory not found: {$logPath}");
            return;
        }

        foreach (glob("{$logPath}/*.log") as $logFile) {
            file_put_contents($logFile, '');
        }

        $this->info('Log files cleared successfully.');
    }
}
