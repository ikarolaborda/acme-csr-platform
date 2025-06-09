<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'donation_number' => $this->donation_number,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->when(!$this->is_anonymous, $this->transaction_id),
            'is_anonymous' => $this->is_anonymous,
            'message' => $this->message,
            'donor_name' => $this->donor_name,
            'user' => $this->when(!$this->is_anonymous, new UserResource($this->whenLoaded('user'))),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'paid_at' => $this->paid_at?->toIsoString(),
            'created_at' => $this->created_at->toIsoString(),
        ];
    }
} 