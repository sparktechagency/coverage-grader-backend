<?php

namespace App\Notifications\Admin;


use App\Models\NotificationAlert;
use App\Notifications\Channels\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CustomAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public NotificationAlert $notificationAlert;

    public function __construct(NotificationAlert $notificationAlert)
    {
        $this->notificationAlert = $notificationAlert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
       // Ensure fcmTokens relationship is loaded
        $notifiable->loadMissing('fcmTokens');

        $channels = ['database'];

        if ($notifiable->fcmTokens && $notifiable->fcmTokens->isNotEmpty()) {
            $channels[] = FirebaseChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'alert_id' => $this->notificationAlert->id,
            'title' => $this->notificationAlert->notification_type,
            'body' => $this->notificationAlert->body,
        ];
    }

    /**
     * Get the array representation of the notification for Firebase.
     *
     * @return array<string, mixed>
     */
    public function toFirebase(object $notifiable): array
    {

        $tokens = $notifiable->fcmTokens->pluck('token')->toArray();

        return [
            'tokens' => $tokens,
            'title' => $this->notificationAlert->notification_type,
            'body' => $this->notificationAlert->body,
            'data' => [
                'alert_id' => (string)$this->notificationAlert->id,
                'type' => 'admin_custom_alert',
            ],
        ];
    }
}
