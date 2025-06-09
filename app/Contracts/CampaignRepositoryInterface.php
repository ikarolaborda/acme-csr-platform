<?php

namespace App\Contracts;

use App\Models\Campaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CampaignRepositoryInterface
{
    /**
     * Find a campaign by ID.
     */
    public function find(int $id): ?Campaign;

    /**
     * Find a campaign by slug.
     */
    public function findBySlug(string $slug): ?Campaign;

    /**
     * Get all campaigns with pagination.
     */
    public function paginate(int $perPage = 12, array $filters = []): LengthAwarePaginator;

    /**
     * Get active campaigns.
     */
    public function getActive(int $limit = null): Collection;

    /**
     * Get featured campaigns.
     */
    public function getFeatured(int $limit = 6): Collection;

    /**
     * Get campaigns by category.
     */
    public function getByCategory(string $category, int $limit = null): Collection;

    /**
     * Get campaigns by user.
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get campaigns by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Create a new campaign.
     */
    public function create(array $data): Campaign;

    /**
     * Update a campaign.
     */
    public function update(int $id, array $data): Campaign;

    /**
     * Delete a campaign.
     */
    public function delete(int $id): bool;

    /**
     * Search campaigns.
     */
    public function search(string $query, array $filters = []): Collection;

    /**
     * Get trending campaigns.
     */
    public function getTrending(int $limit = 6): Collection;

    /**
     * Get campaigns ending soon.
     */
    public function getEndingSoon(int $days = 7, int $limit = 6): Collection;

    /**
     * Increment campaign views.
     */
    public function incrementViews(int $id): void;

    /**
     * Get campaign statistics.
     */
    public function getStatistics(): array;

    /**
     * Enable a campaign.
     */
    public function enable(int $id, int $approvedBy): Campaign;

    /**
     * Disable a campaign.
     */
    public function disable(int $id, ?string $reason = null): Campaign;

    /**
     * Bulk update campaign statuses.
     */
    public function bulkUpdateStatus(array $ids, string $status, ?string $reason = null, ?int $approvedBy = null): int;
} 