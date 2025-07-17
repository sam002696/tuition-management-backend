<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConnectionRequestNotification extends Notification
{
    use Queueable;

    protected array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }


    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'request_id' => $this->data['request_id'],
            'type' => 'connection_request',
            'audience_role' => $notifiable->role,
        ];
    }

    public function toBroadcast(object $notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'request_id' => $this->data['request_id'],
            'type' => 'connection_request',
            'audience_role' => $notifiable->role,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
