<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class BackupTenants extends Command
{
    use TenantAwareCommand, HasATenantsOption;

    protected $signature = 'tenants:backup';
    protected $description = 'Backup tenant databases';

    public function handle() : void
    {
        $tenant = tenant();
        $backupPath = '/home/deploy/backups/' . $tenant->id;
        
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql.gz';
        $filePath = $backupPath . '/' . $filename;

        // Get database configuration
        $config = config('database.connections.tenant');
        
        // Construct pg_dump command with gzip compression
        $command = sprintf(
            'PGPASSWORD="%s" pg_dump -h "%s" -p "%s" -U "%s" "%s" | gzip > "%s"',
            $config['password'],
            $config['host'],
            $config['port'],
            $config['username'],
            $config['database'],
            $filePath
        );

        $this->info("Backing up {$tenant->id} to {$filePath}...");
        
        // Execute backup
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Backup failed for {$tenant->id}");
            return;
        }

        $this->info("Backup successful.");

        // Cleanup old backups (keep latest 5)
        $files = File::files($backupPath);
        usort($files, function ($a, $b) {
            return $b->getMTime() - $a->getMTime();
        });

        $filesToDelete = array_slice($files, 5);

        foreach ($filesToDelete as $file) {
            File::delete($file->getPathname());
            $this->info("Deleted old backup: " . $file->getFilename());
        }
    }
}
