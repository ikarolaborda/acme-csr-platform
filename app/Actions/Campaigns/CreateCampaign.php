<?php

namespace App\Actions\Campaigns;

use App\Contracts\CampaignRepositoryInterface;
use App\Models\Campaign;
use App\Notifications\CampaignCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCampaign
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Create a new campaign.
     *
     * @param array $data
     * @return Campaign
     */
    public function execute(array $data): Campaign
    {
        return DB::transaction(function () use ($data) {
            // Generate slug if not provided
            if (!isset($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            // Create the campaign
            $campaign = $this->campaignRepository->create($data);

            // Handle any additional setup (e.g., categories, tags, etc.)
            // This is where you'd handle relationships if needed

            // Load relationships
            $campaign->load('user');
            
            // Send notification
            $campaign->user->notify(new CampaignCreated($campaign));

            return $campaign;
        });
    }

    /**
     * Generate a unique slug for the campaign.
     *
     * @param string $title
     * @return string
     */
    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Campaign::where('slug', 'LIKE', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }
} 