<?php

namespace App\Repositories\Cache;

use App\Contracts\CampaignRepositoryInterface;
use App\Models\Campaign;
use App\Repositories\Eloquent\CampaignRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CampaignCacheRepository implements CampaignRepositoryInterface
{
    protected CampaignRepository $eloquentRepository;
    protected int $cacheTime = 3600; // 1 hour

    public function __construct(CampaignRepository $eloquentRepository)
    {
        $this->eloquentRepository = $eloquentRepository;
    }

    public function find(int $id): ?Campaign
    {
        return Cache::tags(['campaigns'])->remember("find.{$id}", $this->cacheTime, function () use ($id) {
            return $this->eloquentRepository->find($id);
        });
    }

    public function findBySlug(string $slug): ?Campaign
    {
        return Cache::tags(['campaigns'])->remember("slug.{$slug}", $this->cacheTime, function () use ($slug) {
            return $this->eloquentRepository->findBySlug($slug);
        });
    }

    public function paginate(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        // Don't cache paginated results as they change frequently
        return $this->eloquentRepository->paginate($perPage, $filters);
    }

    public function getActive(int $limit = null): Collection
    {
        $cacheKey = "active." . ($limit ?? 'all');
        return Cache::tags(['campaigns'])->remember($cacheKey, $this->cacheTime, function () use ($limit) {
            return $this->eloquentRepository->getActive($limit);
        });
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return Cache::tags(['campaigns'])->remember("featured.{$limit}", $this->cacheTime, function () use ($limit) {
            return $this->eloquentRepository->getFeatured($limit);
        });
    }

    public function getByCategory(string $category, int $limit = null): Collection
    {
        $cacheKey = "category.{$category}." . ($limit ?? 'all');
        return Cache::tags(['campaigns'])->remember($cacheKey, $this->cacheTime, function () use ($category, $limit) {
            return $this->eloquentRepository->getByCategory($category, $limit);
        });
    }

    public function getByUser(int $userId): Collection
    {
        return Cache::tags(['campaigns'])->remember("user.{$userId}", $this->cacheTime, function () use ($userId) {
            return $this->eloquentRepository->getByUser($userId);
        });
    }

    public function getByStatus(string $status): Collection
    {
        return Cache::tags(['campaigns'])->remember("status.{$status}", $this->cacheTime, function () use ($status) {
            return $this->eloquentRepository->getByStatus($status);
        });
    }

    public function create(array $data): Campaign
    {
        $campaign = $this->eloquentRepository->create($data);
        $this->clearCache();
        return $campaign;
    }

    public function update(int $id, array $data): Campaign
    {
        $campaign = $this->eloquentRepository->update($id, $data);
        $this->clearCache();
        Cache::tags(['campaigns'])->forget("find.{$id}");
        Cache::tags(['campaigns'])->forget("slug.{$campaign->slug}");
        return $campaign;
    }

    public function delete(int $id): bool
    {
        $campaign = $this->find($id);
        $result = $this->eloquentRepository->delete($id);
        if ($result) {
            $this->clearCache();
            Cache::tags(['campaigns'])->forget("find.{$id}");
            if ($campaign) {
                Cache::tags(['campaigns'])->forget("slug.{$campaign->slug}");
            }
        }
        return $result;
    }

    public function search(string $query, array $filters = []): Collection
    {
        // Don't cache search results as they are too varied
        return $this->eloquentRepository->search($query, $filters);
    }

    public function getTrending(int $limit = 6): Collection
    {
        return Cache::tags(['campaigns'])->remember("trending.{$limit}", $this->cacheTime / 2, function () use ($limit) {
            return $this->eloquentRepository->getTrending($limit);
        });
    }

    public function getEndingSoon(int $days = 7, int $limit = 6): Collection
    {
        return Cache::tags(['campaigns'])->remember("ending_soon.{$days}.{$limit}", $this->cacheTime / 2, function () use ($days, $limit) {
            return $this->eloquentRepository->getEndingSoon($days, $limit);
        });
    }

    public function incrementViews(int $id): void
    {
        $this->eloquentRepository->incrementViews($id);
        // Don't clear cache for view increments to avoid cache thrashing
    }

    public function getStatistics(): array
    {
        return Cache::tags(['campaigns'])->remember("statistics", $this->cacheTime, function () {
            return $this->eloquentRepository->getStatistics();
        });
    }

    public function enable(int $id, int $approvedBy): Campaign
    {
        $campaign = $this->eloquentRepository->enable($id, $approvedBy);
        $this->clearCache();
        Cache::tags(['campaigns'])->forget("find.{$id}");
        Cache::tags(['campaigns'])->forget("slug.{$campaign->slug}");
        return $campaign;
    }

    public function disable(int $id, ?string $reason = null): Campaign
    {
        $campaign = $this->eloquentRepository->disable($id, $reason);
        $this->clearCache();
        Cache::tags(['campaigns'])->forget("find.{$id}");
        Cache::tags(['campaigns'])->forget("slug.{$campaign->slug}");
        return $campaign;
    }

    public function bulkUpdateStatus(array $ids, string $status, ?string $reason = null, ?int $approvedBy = null): int
    {
        $result = $this->eloquentRepository->bulkUpdateStatus($ids, $status, $reason, $approvedBy);
        $this->clearCache();
        foreach ($ids as $id) {
            Cache::tags(['campaigns'])->forget("find.{$id}");
        }
        return $result;
    }

    protected function clearCache(): void
    {
        Cache::tags(['campaigns'])->flush();
    }
} 