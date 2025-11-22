<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\PolicyCategoryResource;
use App\Http\Resources\Admin\StatesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avgScores = $this->avg_scores ? json_decode($this->avg_scores) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'pros' => $this->pros,
            'cons' => $this->cons,
            'price' => $this->price,
            'is_sponsored' => $this->is_sponsored,
            'sponsored_url' => $this->sponsored_url,
            'status' => $this->status,
            'about' => $this->about,
            'states_count' => $this->whenCounted('states', function () {
                return $this->states_count;
            }),
            'reviews_count' => $this->review_count,
            'avg_overall_rating' => $this->avg_overall_rating,
            'avg_grade' => $this->avg_grade,
            'formatted_overall_avg_score' =>"{$this->avg_overall_rating}/5 ({$this->avg_grade})",
            'avg_score' => $avgScores,
            'avg_trust' => $avgScores?->trust,
            'avg_claims' => $avgScores?->claims,
            'avg_pricing' => $avgScores?->pricing,
            'avg_service' => $avgScores?->service,
            'avg_coverage' => $avgScores?->coverage,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'policies' => PolicyCategoryResource::collection($this->whenLoaded('policyCategories')),
            'states' => StatesResource::collection($this->whenLoaded('states')),
        ];
    }
}
