<?php

namespace App\Actions;

use App\Actions\Campaigns\UpdateCampaign;
use App\DTOs\CampaignData;
use App\Models\Campaign;

class UpdateCampaignAction
{
    public function __construct(
        protected UpdateCampaign $updateCampaign
    ) {}

    /**
     * Execute the campaign update.
     */
    public function execute(Campaign $campaign, CampaignData $data): Campaign
    {
        return $this->updateCampaign->execute($campaign->id, $data->toArray());
    }
} 