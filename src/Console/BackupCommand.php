<?php

namespace ShahzadThathal\IncrementalBackup\Console;

use Illuminate\Console\Command;
use ShahzadThathal\IncrementalBackup\BackupManager;

class BackupCommand extends Command
{
    protected $signature = 'incremental-backup:run {type=all}';
    protected $description = 'Run incremental backups';

    public function handle(BackupManager $manager)
    {
        $type = $this->argument('type');
        $disk = config('incremental-backup.default_disk');
        $backupPath = config('incremental-backup.backup_path');
        $exclude = config('incremental-backup.exclude');
        $cleanupDays = config('incremental-backup.cleanup_days');

        $backupDetails = '';

        if ($type === 'all' || $type === 'files') {
            $backupDetails .= $manager->backupFiles($disk, base_path(), $backupPath, $exclude) . PHP_EOL;
        }

        if ($type === 'all' || $type === 'database') {
            $backupDetails .= $manager->backupDatabase($disk, $backupPath) . PHP_EOL;
        }

        // Notify developer
        $manager->notifyCompletion($backupDetails);

        // Cleanup old backups
        $cleanupMessage = $manager->deleteOldBackups($disk, $backupPath, $cleanupDays);
        $this->info($cleanupMessage);

        $this->info('Backup process completed.');
    }

}
