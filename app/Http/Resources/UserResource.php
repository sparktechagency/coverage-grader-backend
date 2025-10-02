<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'main_role' => $this->whenLoaded('roles', fn() => $this->getRoleNames()->first()),
            'avatar' => $this->avatar,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'joined_at' => $this->joined_at ? Carbon::parse($this->joined_at)->format('Y-m-d') : Carbon::parse($this->created_at)->format('Y-m-d') ?? null,
            'last_login_at' => $this->last_login_at ? Carbon::parse($this->last_login_at)->format('Y-m-d') : null,
            // 'last_login_human' => $this->last_login_at ? Carbon::parse($this->last_login_at)->diffForHumans() : null,
            'first_name' => $this->first_name,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
