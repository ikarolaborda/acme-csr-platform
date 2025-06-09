<?php

namespace App\Contracts;

use App\Models\Donation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DonationRepositoryInterface
{
    /**
     * Find a donation by ID.
     */
    public function find(int $id): ?Donation;

    /**
     * Find a donation by donation number.
     */
    public function findByDonationNumber(string $donationNumber): ?Donation;

    /**
     * Get all donations with pagination.
     */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    /**
     * Get donations by user.
     */
    public function getByUser(int $userId, int $limit = null): Collection;

    /**
     * Get donations by campaign.
     */
    public function getByCampaign(int $campaignId, int $limit = null): Collection;

    /**
     * Get donations by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Create a new donation.
     */
    public function create(array $data): Donation;

    /**
     * Update a donation.
     */
    public function update(int $id, array $data): Donation;

    /**
     * Delete a donation.
     */
    public function delete(int $id): bool;

    /**
     * Get recent donations.
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get top donations.
     */
    public function getTopDonations(int $limit = 10): Collection;

    /**
     * Get donation statistics.
     */
    public function getStatistics(array $filters = []): array;

    /**
     * Get user donation history.
     */
    public function getUserDonationHistory(int $userId): Collection;

    /**
     * Get campaign donation summary.
     */
    public function getCampaignDonationSummary(int $campaignId): array;

    /**
     * Mark donation as completed.
     */
    public function markAsCompleted(int $id, string $transactionId, array $paymentDetails = []): Donation;

    /**
     * Mark donation as failed.
     */
    public function markAsFailed(int $id, string $reason): Donation;
} 