<?php

namespace App\Actions\Donations;

use App\Contracts\CampaignRepositoryInterface;
use App\Contracts\DonationRepositoryInterface;
use App\Models\Donation;
use App\Notifications\DonationConfirmation;
use Illuminate\Support\Facades\DB;

class CreateDonation
{
    public function __construct(
        protected DonationRepositoryInterface $donationRepository,
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Execute the donation creation action.
     */
    public function execute(array $data): Donation
    {
        return DB::transaction(function () use ($data) {
            // Verify campaign exists and is active
            $campaign = $this->campaignRepository->find($data['campaign_id']);
            
            if (!$campaign || !$campaign->isActive()) {
                throw new \Exception('Campaign is not available for donations.');
            }

            // Add authenticated user ID
            $data['user_id'] = auth()->id();
            
            // Set default values
            $data['status'] = 'pending';
            $data['currency'] = $data['currency'] ?? 'USD';
            
            // Create the donation
            $donation = $this->donationRepository->create($data);
            
            // Process payment (mock for now)
            // In a real application, this would integrate with a payment gateway
            $donation->update([
                'status' => 'completed',
                'payment_method' => 'mock',
                'transaction_id' => 'MOCK-' . uniqid()
            ]);

            // Update campaign totals
            $donation->campaign->updateTotals();
            
            // Send confirmation notification
            $donation->user->notify(new DonationConfirmation($donation));

            return $donation->fresh(['campaign', 'user']);
        });
    }

    /**
     * Generate a unique donation number.
     */
    protected function generateDonationNumber(): string
    {
        do {
            $number = 'DON-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Donation::where('donation_number', $number)->exists());

        return $number;
    }
} 