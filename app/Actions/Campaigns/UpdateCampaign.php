<?php

namespace App\Actions\Campaigns;

use App\Contracts\CampaignRepositoryInterface;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateCampaign
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Execute the campaign update action.
     */
    public function execute(int $id, array $data): Campaign
    {
        return DB::transaction(function () use ($id, $data) {
            // If title is being updated and slug is not provided, generate new slug
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
            }
            
            // Update the campaign
            $campaign = $this->campaignRepository->update($id, $data);
            
            return $campaign;
        });
    }

    /**
     * Generate a unique slug for the campaign.
     */
    protected function generateUniqueSlug(string $title, int $excludeId): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $existing = $this->campaignRepository->findBySlug($slug);
            
            if (!$existing || $existing->id === $excludeId) {
                break;
            }
            
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
} 