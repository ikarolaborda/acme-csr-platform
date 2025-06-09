<?php

namespace App\Repositories\Cache;

use App\Contracts\DonationRepositoryInterface;
use App\Models\Donation;
use App\Repositories\Eloquent\DonationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DonationCacheRepository implements DonationRepositoryInterface
{
    protected string $cachePrefix = 'donations:';
    protected int $cacheTTL = 3600; // 1 hour

    public function __construct(
        protected DonationRepository $eloquentRepository
    ) {}

    /**
     * Find a donation by ID.
     */
    public function find(int $id): ?Donation
    {
        return Cache::remember(
            $this->cachePrefix . 'id:' . $id,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->find($id)
        );
    }

    /**
     * Find a donation by donation number.
     */
    public function findByDonationNumber(string $donationNumber): ?Donation
    {
        return Cache::remember(
            $this->cachePrefix . 'number:' . $donationNumber,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->findByDonationNumber($donationNumber)
        );
    }

    /**
     * Get all donations with pagination.
     */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = $this->cachePrefix . 'paginate:' . $perPage . ':' . md5(serialize($filters));
        
        return Cache::remember(
            $cacheKey,
            300, // 5 minutes for paginated results
            fn() => $this->eloquentRepository->paginate($perPage, $filters)
        );
    }

    /**
     * Get donations by user.
     */
    public function getByUser(int $userId, int $limit = null): Collection
    {
        $cacheKey = $this->cachePrefix . 'user:' . $userId . ':' . ($limit ?? 'all');
        
        return Cache::remember(
            $cacheKey,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getByUser($userId, $limit)
        );
    }

    /**
     * Get donations by campaign.
     */
    public function getByCampaign(int $campaignId, int $limit = null): Collection
    {
        $cacheKey = $this->cachePrefix . 'campaign:' . $campaignId . ':' . ($limit ?? 'all');
        
        return Cache::remember(
            $cacheKey,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getByCampaign($campaignId, $limit)
        );
    }

    /**
     * Get donations by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'status:' . $status,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getByStatus($status)
        );
    }

    /**
     * Create a new donation.
     */
    public function create(array $data): Donation
    {
        $donation = $this->eloquentRepository->create($data);
        $this->clearUserDonationsCache($donation->user_id);
        $this->clearCampaignDonationsCache($donation->campaign_id);
        return $donation;
    }

    /**
     * Update a donation.
     */
    public function update(int $id, array $data): Donation
    {
        $donation = $this->eloquentRepository->update($id, $data);
        $this->clearDonationCache($id);
        $this->clearDonationCacheByNumber($donation->donation_number);
        $this->clearUserDonationsCache($donation->user_id);
        $this->clearCampaignDonationsCache($donation->campaign_id);
        return $donation;
    }

    /**
     * Delete a donation.
     */
    public function delete(int $id): bool
    {
        $donation = $this->find($id);
        $result = $this->eloquentRepository->delete($id);
        
        if ($result && $donation) {
            $this->clearDonationCache($id);
            $this->clearDonationCacheByNumber($donation->donation_number);
            $this->clearUserDonationsCache($donation->user_id);
            $this->clearCampaignDonationsCache($donation->campaign_id);
        }
        
        return $result;
    }

    /**
     * Get recent donations.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'recent:' . $limit,
            600, // 10 minutes
            fn() => $this->eloquentRepository->getRecent($limit)
        );
    }

    /**
     * Get top donations.
     */
    public function getTopDonations(int $limit = 10): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'top:' . $limit,
            1800, // 30 minutes
            fn() => $this->eloquentRepository->getTopDonations($limit)
        );
    }

    /**
     * Get donation statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $cacheKey = $this->cachePrefix . 'statistics:' . md5(serialize($filters));
        
        return Cache::remember(
            $cacheKey,
            3600, // 1 hour
            fn() => $this->eloquentRepository->getStatistics($filters)
        );
    }

    /**
     * Get user donation history.
     */
    public function getUserDonationHistory(int $userId): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'history:user:' . $userId,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getUserDonationHistory($userId)
        );
    }

    /**
     * Get campaign donation summary.
     */
    public function getCampaignDonationSummary(int $campaignId): array
    {
        return Cache::remember(
            $this->cachePrefix . 'summary:campaign:' . $campaignId,
            1800, // 30 minutes
            fn() => $this->eloquentRepository->getCampaignDonationSummary($campaignId)
        );
    }

    /**
     * Mark donation as completed.
     */
    public function markAsCompleted(int $id, string $transactionId, array $paymentDetails = []): Donation
    {
        $donation = $this->eloquentRepository->markAsCompleted($id, $transactionId, $paymentDetails);
        $this->clearDonationCache($id);
        $this->clearDonationCacheByNumber($donation->donation_number);
        $this->clearUserDonationsCache($donation->user_id);
        $this->clearCampaignDonationsCache($donation->campaign_id);
        Cache::forget($this->cachePrefix . 'recent:*');
        Cache::forget($this->cachePrefix . 'statistics:*');
        return $donation;
    }

    /**
     * Mark donation as failed.
     */
    public function markAsFailed(int $id, string $reason): Donation
    {
        $donation = $this->eloquentRepository->markAsFailed($id, $reason);
        $this->clearDonationCache($id);
        $this->clearDonationCacheByNumber($donation->donation_number);
        $this->clearUserDonationsCache($donation->user_id);
        $this->clearCampaignDonationsCache($donation->campaign_id);
        return $donation;
    }

    /**
     * Clear specific donation cache.
     */
    protected function clearDonationCache(int $id): void
    {
        Cache::forget($this->cachePrefix . 'id:' . $id);
    }

    /**
     * Clear donation cache by number.
     */
    protected function clearDonationCacheByNumber(string $number): void
    {
        Cache::forget($this->cachePrefix . 'number:' . $number);
    }

    /**
     * Clear user donations cache.
     */
    protected function clearUserDonationsCache(int $userId): void
    {
        Cache::forget($this->cachePrefix . 'user:' . $userId . ':*');
        Cache::forget($this->cachePrefix . 'history:user:' . $userId);
    }

    /**
     * Clear campaign donations cache.
     */
    protected function clearCampaignDonationsCache(int $campaignId): void
    {
        Cache::forget($this->cachePrefix . 'campaign:' . $campaignId . ':*');
        Cache::forget($this->cachePrefix . 'summary:campaign:' . $campaignId);
    }

    /**
     * Clear all donation-related cache.
     */
    protected function clearCache(): void
    {
        Cache::tags(['donations'])->flush();
    }
} 