<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'category' => $this->category,
            'goal_amount' => $this->goal_amount,
            'current_amount' => $this->current_amount,
            'progress_percentage' => $this->progress_percentage,
            'donors_count' => $this->donors_count,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'days_remaining' => $this->days_remaining,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_featured' => $this->is_featured,
            'featured_image_url' => $this->featured_image_url,
            'images' => $this->images,
            'documents' => $this->documents,
            'views_count' => $this->views_count,
            'impact_description' => $this->impact_description,
            'milestones' => $this->milestones,
            'user' => new UserResource($this->whenLoaded('user')),
            'donations' => DonationResource::collection($this->whenLoaded('donations')),
            'created_at' => $this->created_at->toIsoString(),
            'updated_at' => $this->updated_at->toIsoString(),
            'approved_at' => $this->approved_at?->toIsoString(),
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
        ];
    }
} 