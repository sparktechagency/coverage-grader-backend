<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'message' => substr($this->message, 0, 50) . (strlen($this->message) > 50 ? ' ...' : ''),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at,
        ];
    }
}
