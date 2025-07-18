<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
// use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TuitionEventNotification extends Notification
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

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
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

    /**
     * Database format
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'event_id' => $this->data['event_id'],
            'type' => 'tuition_event',
            'audience_role' => $notifiable->role,
        ];
    }


    /**
     * Broadcast format
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'event_id' => $this->data['event_id'],
            'type' => 'tuition_event',
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
