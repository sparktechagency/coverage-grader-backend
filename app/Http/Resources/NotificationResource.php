<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $baseData = [
            'id' => $this->id,
            'is_read' => $this->read_at !== null,
            'created_at_human' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at,
        ];
        switch ($this->type) {
            case 'App\Notifications\Admin\CustomAlert':
                return array_merge($baseData, [
                    'type' => 'admin_notification_alert',
                    'alert_id' => $this->data['alert_id'],
                    'title' => $this->data['title'] ?? null,
                    'message' => $this->data['body'],
                ]);

            default:
                $title = $this->data['title'] ?? $this->data['subject'] ?? 'New Notification';
                $message = $this->data['message'] ?? $this->data['body'] ?? 'You have a new notification.';

                $type = Str::snake(class_basename($this->type));

                return array_merge($baseData, [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                ]);
        }
    }
}
