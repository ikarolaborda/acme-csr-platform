<?php

namespace App\Repositories\Eloquent;

use App\Contracts\CampaignRepositoryInterface;
use App\Models\Campaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function __construct(
        protected Campaign $model
    ) {}

    /**
     * Find a campaign by ID.
     */
    public function find(int $id): ?Campaign
    {
        return $this->model->with(['user', 'donations'])->find($id);
    }

    /**
     * Find a campaign by slug.
     */
    public function findBySlug(string $slug): ?Campaign
    {
        return $this->model->with(['user', 'donations'])->where('slug', $slug)->first();
    }

    /**
     * Get all campaigns with pagination.
     */
    public function paginate(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('donors_count', 'desc');
                    break;
                case 'ending_soon':
                    $query->where('end_date', '>=', now())
                          ->orderBy('end_date', 'asc');
                    break;
                case 'goal_reached':
                    $query->orderByRaw('current_amount / goal_amount DESC');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get active campaigns.
     */
    public function getActive(int $limit = null): Collection
    {
        $query = $this->model->active()->with('user');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get featured campaigns.
     */
    public function getFeatured(int $limit = 6): Collection
    {
        return $this->model->featured()
            ->active()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get campaigns by category.
     */
    public function getByCategory(string $category, int $limit = null): Collection
    {
        $query = $this->model->byCategory($category)
            ->active()
            ->with('user');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get campaigns by user.
     */
    public function getByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get campaigns by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with('user')
            ->get();
    }

    /**
     * Create a new campaign.
     */
    public function create(array $data): Campaign
    {
        return $this->model->create($data);
    }

    /**
     * Update a campaign.
     */
    public function update(int $id, array $data): Campaign
    {
        $campaign = $this->find($id);
        $campaign->update($data);
        return $campaign->fresh();
    }

    /**
     * Delete a campaign.
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Search campaigns.
     */
    public function search(string $query, array $filters = []): Collection
    {
        $searchQuery = $this->model->search($query)->with('user');

        if (isset($filters['category'])) {
            $searchQuery->where('category', $filters['category']);
        }

        if (isset($filters['status'])) {
            $searchQuery->where('status', $filters['status']);
        }

        return $searchQuery->limit(50)->get();
    }

    /**
     * Get trending campaigns.
     */
    public function getTrending(int $limit = 6): Collection
    {
        return $this->model->active()
            ->with('user')
            ->orderBy('views_count', 'desc')
            ->orderBy('donors_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get campaigns ending soon.
     */
    public function getEndingSoon(int $days = 7, int $limit = 6): Collection
    {
        return $this->model->active()
            ->with('user')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now())
            ->orderBy('end_date', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Increment campaign views.
     */
    public function incrementViews(int $id): void
    {
        $campaign = $this->model->find($id);
        if ($campaign) {
            $campaign->incrementViews();
        }
    }

    /**
     * Get campaign statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_campaigns' => $this->model->count(),
            'active_campaigns' => $this->model->active()->count(),
            'total_raised' => $this->model->sum('current_amount'),
            'total_donors' => $this->model->sum('donors_count'),
            'success_rate' => $this->model->whereRaw('current_amount >= goal_amount')->count() / max($this->model->count(), 1) * 100,
            'categories' => $this->model->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
        ];
    }

    /**
     * Enable a campaign.
     */
    public function enable(int $id, int $approvedBy): Campaign
    {
        $campaign = $this->find($id);
        $campaign->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
        return $campaign->fresh();
    }

    /**
     * Disable a campaign.
     */
    public function disable(int $id, ?string $reason = null): Campaign
    {
        $campaign = $this->find($id);
        $updateData = ['status' => 'cancelled'];
        
        if ($reason) {
            $updateData['rejection_reason'] = $reason;
        }
        
        $campaign->update($updateData);
        return $campaign->fresh();
    }

    /**
     * Bulk update campaign statuses.
     */
    public function bulkUpdateStatus(array $ids, string $status, ?string $reason = null, ?int $approvedBy = null): int
    {
        $updateData = ['status' => $status];
        
        if ($status === 'active' && $approvedBy) {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = $approvedBy;
        }
        
        if ($reason) {
            $updateData['rejection_reason'] = $reason;
        }
        
        return $this->model->whereIn('id', $ids)->update($updateData);
    }
} 