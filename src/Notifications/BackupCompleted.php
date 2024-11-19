<?php

namespace ShahzadThathal\IncrementalBackup\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification
{
    use Queueable;

    protected $backupDetails;

    /**
     * Create a new notification instance.
     *
     * @param array $backupDetails
     */
    public function __construct(array $backupDetails)
    {
        $this->backupDetails = $backupDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Backup Completed Successfully')
            ->greeting('Hello!')
            ->line('Your backup has been completed successfully.')
            ->line('Backup details:')
            ->line('Folder: ' . $this->backupDetails['folder'])
            ->line('Size: ' . $this->backupDetails['size'])
            ->line('Created At: ' . $this->backupDetails['created_at'])
            ->action('View Backup', $this->backupDetails['url'] ?? '#')
            ->line('Thank you for using our package!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'folder' => $this->backupDetails['folder'],
            'size' => $this->backupDetails['size'],
            'created_at' => $this->backupDetails['created_at'],
        ];
    }
}
