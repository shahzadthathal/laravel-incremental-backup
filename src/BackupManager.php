<?php

namespace ShahzadThathal\IncrementalBackup;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

use ShahzadThathal\IncrementalBackup\Notifications\BackupCompleted;
use Illuminate\Support\Facades\Notification;

class BackupManager
{
    public function backupFiles(string $disk = 'local', string $sourcePath = '', string $backupPath = '', array $exclude = [])
    {
        $adapter = Storage::disk($disk);

        // Prepare exclusion flags for rsync
        $excludeFlags = [];
        foreach ($exclude as $excludedPath) {
            $excludeFlags[] = "--exclude={$excludedPath}";
        }

        // Use rsync for incremental backup
        $command = array_merge(
            ['rsync', '-av', '--progress'],
            $excludeFlags,
            [$sourcePath, $backupPath]
        );

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        // Notify on successful backup
        $backupDetails = [
            'folder' => $backupPath,
            'size' => $this->getBackupSize($disk, $backupPath),
            'created_at' => now(),
            'url' => Storage::disk($disk)->url($backupPath),
        ];

        $this->completeBackup($backupDetails);

        return "File backup completed to {$disk}";
    }


    public function backupDatabase(string $disk = 'local', string $backupPath = '')
    {
        $dbName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        
        $filename = "{$backupPath}/{$dbName}-" . date('Y-m-d-H-i-s') . ".sql";
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($dbName),
            escapeshellarg($filename)
        );

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        // Store in the disk
        Storage::disk($disk)->putFileAs('', new \SplFileInfo($filename), basename($filename));

        // Notify on successful backup
        $backupDetails = [
            'folder' => $backupPath,
            'size' => Storage::disk($disk)->size(basename($filename)) . ' bytes',
            'created_at' => now(),
            'url' => Storage::disk($disk)->url(basename($filename)),
        ];

        $this->completeBackup($backupDetails);

        return "Database backup completed to {$disk}";
    }

    public function deleteOldBackups(string $disk = 'local', string $backupPath = '', int $days = 7)
    {
        $files = Storage::disk($disk)->files($backupPath);
        $thresholdDate = now()->subDays($days);

        foreach ($files as $file) {
            $lastModified = Storage::disk($disk)->lastModified($file);
            if ($lastModified < $thresholdDate->timestamp) {
                Storage::disk($disk)->delete($file);
            }
        }

        return "Old backups older than {$days} days have been deleted.";
    }

     public function completeBackup($backupDetails)
    {
        // Assuming developer_email is set in the config
        $developerEmail = config('incremental-backup.developer_email');

        // Send the notification
        Notification::route('mail', $developerEmail)
            ->notify(new BackupCompleted($backupDetails));
    }

    // Helper method to calculate backup size
    private function getBackupSize(string $disk, string $path)
    {
        $files = Storage::disk($disk)->allFiles($path);
        $size = 0;

        foreach ($files as $file) {
            $size += Storage::disk($disk)->size($file);
        }

        return $size . ' bytes';
    }

}
