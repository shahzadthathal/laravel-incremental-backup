<?php

return [
    'backup_folder_prefix' => env('BACKUP_FOLDER_PREFIX', 'app-backup'), // Default folder prefix
    'default_disk' => env('BACKUP_DISK', 'local'), // Default storage disk
    'backup_path' => env('BACKUP_PATH', 'backups'), // Path where backups are stored
    'exclude' => [
        'vendor',
        'modules',
        'node_modules',
        'storage/logs',
    ], // Excluded directories
    'developer_email' => env('BACKUP_DEVELOPER_EMAIL', 'developer@example.com'), // Developer email for notifications
    'cleanup_days' => env('BACKUP_CLEANUP_DAYS', 7), // Days after which backups are deleted
];
