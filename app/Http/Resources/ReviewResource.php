<?php

namespace App\Http\Resources;

use App\Http\Resources\InsuranceProviderResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isPrivilegedUser = $user && $user->hasAnyRole(['admin', 'super-admin']);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'provider_id' => $this->provider_id,
            'state_id' => $this->state_id,
            'overall_rating' => $this->overall_rating,
            'status' => $this->status,
            'comment' => $this->comment,
            'scores' => $this->scores,
            'display_score' => $isPrivilegedUser ? $this->formatted_score : $this->average_score,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'provider' => new InsuranceProviderResource($this->whenLoaded('provider')),
            'state' => $this->whenLoaded('state'),

        ];
    }
}
