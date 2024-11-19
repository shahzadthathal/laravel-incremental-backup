<?php

namespace ShahzadThathal\IncrementalBackup;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;


class BackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BackupManager::class, function () {
            return new BackupManager();
        });
    }

    public function boot()
    {
        // Publish configuration or commands here if needed
        $this->publishes([
            __DIR__ . '/../config/incremental-backup.php' => config_path('incremental-backup.php'),
        ], 'config');
    }
}
