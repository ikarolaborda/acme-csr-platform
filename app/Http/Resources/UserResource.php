<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'position' => $this->position,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'total_donated' => $this->total_donated,
            'campaigns_created' => $this->campaigns_created,
            'last_login_at' => $this->last_login_at?->toIsoString(),
            'created_at' => $this->created_at->toIsoString(),
        ];
    }
} 