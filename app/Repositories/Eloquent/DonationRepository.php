<?php

namespace App\Repositories\Eloquent;

use App\Contracts\DonationRepositoryInterface;
use App\Models\Donation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DonationRepository implements DonationRepositoryInterface
{
    public function __construct(
        protected Donation $model
    ) {}

    /**
     * Find a donation by ID.
     */
    public function find(int $id): ?Donation
    {
        return $this->model->with(['user', 'campaign'])->find($id);
    }

    /**
     * Find a donation by donation number.
     */
    public function findByDonationNumber(string $donationNumber): ?Donation
    {
        return $this->model->with(['user', 'campaign'])
            ->where('donation_number', $donationNumber)
            ->first();
    }

    /**
     * Get all donations with pagination.
     */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'campaign']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get donations by user.
     */
    public function getByUser(int $userId, int $limit = null): Collection
    {
        $query = $this->model->where('user_id', $userId)
            ->with(['campaign'])
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get donations by campaign.
     */
    public function getByCampaign(int $campaignId, int $limit = null): Collection
    {
        $query = $this->model->where('campaign_id', $campaignId)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get donations by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['user', 'campaign'])
            ->get();
    }

    /**
     * Create a new donation.
     */
    public function create(array $data): Donation
    {
        return $this->model->create($data);
    }

    /**
     * Update a donation.
     */
    public function update(int $id, array $data): Donation
    {
        $donation = $this->find($id);
        $donation->update($data);
        return $donation->fresh();
    }

    /**
     * Delete a donation.
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Get recent donations.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->completed()
            ->with(['user', 'campaign'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top donations.
     */
    public function getTopDonations(int $limit = 10): Collection
    {
        return $this->model->completed()
            ->with(['user', 'campaign'])
            ->orderBy('amount', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get donation statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = $this->model->completed();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'average_amount' => $query->avg('amount') ?? 0,
            'unique_donors' => $query->distinct('user_id')->count('user_id'),
            'unique_campaigns' => $query->distinct('campaign_id')->count('campaign_id'),
        ];
    }

    /**
     * Get user donation history.
     */
    public function getUserDonationHistory(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['campaign'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get campaign donation summary.
     */
    public function getCampaignDonationSummary(int $campaignId): array
    {
        $donations = $this->model->where('campaign_id', $campaignId)->completed();

        return [
            'total_amount' => $donations->sum('amount'),
            'total_count' => $donations->count(),
            'average_amount' => $donations->avg('amount') ?? 0,
            'unique_donors' => $donations->distinct('user_id')->count('user_id'),
            'anonymous_count' => $donations->where('is_anonymous', true)->count(),
            'top_donation' => $donations->max('amount') ?? 0,
        ];
    }

    /**
     * Mark donation as completed.
     */
    public function markAsCompleted(int $id, string $transactionId, array $paymentDetails = []): Donation
    {
        $donation = $this->find($id);
        $donation->markAsCompleted($transactionId, $paymentDetails);
        return $donation->fresh();
    }

    /**
     * Mark donation as failed.
     */
    public function markAsFailed(int $id, string $reason): Donation
    {
        $donation = $this->find($id);
        $donation->markAsFailed($reason);
        return $donation->fresh();
    }
} 