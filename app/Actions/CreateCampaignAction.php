<?php

namespace App\Actions;

use App\Actions\Campaigns\CreateCampaign;
use App\DTOs\CampaignData;
use App\Models\Campaign;

class CreateCampaignAction
{
    public function __construct(
        protected CreateCampaign $createCampaign
    ) {}

    /**
     * Execute the campaign creation.
     */
    public function execute(CampaignData $data): Campaign
    {
        return $this->createCampaign->execute($data->toArray());
    }
} 