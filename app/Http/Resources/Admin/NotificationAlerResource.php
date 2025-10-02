<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationAlerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'body' => $this->body,
            'notification_type' => $this->notification_type,
            'recipient_type' => $this->recipient_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
