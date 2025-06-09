<?php

namespace App\Repositories\Cache;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UserCacheRepository implements UserRepositoryInterface
{
    protected string $cachePrefix = 'users:';
    protected int $cacheTTL = 3600; // 1 hour

    public function __construct(
        protected UserRepository $eloquentRepository
    ) {}

    /**
     * Find a user by ID.
     */
    public function find(int $id): ?User
    {
        return Cache::remember(
            $this->cachePrefix . 'id:' . $id,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->find($id)
        );
    }

    /**
     * Find a user by employee ID.
     */
    public function findByEmployeeId(string $employeeId): ?User
    {
        return Cache::remember(
            $this->cachePrefix . 'employee_id:' . $employeeId,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->findByEmployeeId($employeeId)
        );
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return Cache::remember(
            $this->cachePrefix . 'email:' . $email,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->findByEmail($email)
        );
    }

    /**
     * Get all users with pagination.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = $this->cachePrefix . 'paginate:' . $perPage . ':' . md5(serialize($filters));
        
        return Cache::remember(
            $cacheKey,
            300, // 5 minutes for paginated results
            fn() => $this->eloquentRepository->paginate($perPage, $filters)
        );
    }

    /**
     * Get users by role.
     */
    public function getByRole(string $role): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'role:' . $role,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getByRole($role)
        );
    }

    /**
     * Get active users.
     */
    public function getActive(): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'active',
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getActive()
        );
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        $user = $this->eloquentRepository->create($data);
        $this->clearCache();
        return $user;
    }

    /**
     * Update a user.
     */
    public function update(int $id, array $data): User
    {
        $user = $this->eloquentRepository->update($id, $data);
        $this->clearUserCache($id);
        $this->clearUserCacheByEmail($user->email);
        $this->clearUserCacheByEmployeeId($user->employee_id);
        return $user;
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        $result = $this->eloquentRepository->delete($id);
        
        if ($result && $user) {
            $this->clearUserCache($id);
            $this->clearUserCacheByEmail($user->email);
            $this->clearUserCacheByEmployeeId($user->employee_id);
        }
        
        return $result;
    }

    /**
     * Get top donors.
     */
    public function getTopDonors(int $limit = 10): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'top_donors:' . $limit,
            $this->cacheTTL,
            fn() => $this->eloquentRepository->getTopDonors($limit)
        );
    }

    /**
     * Search users.
     */
    public function search(string $query): Collection
    {
        return Cache::remember(
            $this->cachePrefix . 'search:' . md5($query),
            300, // 5 minutes for search results
            fn() => $this->eloquentRepository->search($query)
        );
    }

    /**
     * Clear specific user cache.
     */
    protected function clearUserCache(int $id): void
    {
        Cache::forget($this->cachePrefix . 'id:' . $id);
    }

    /**
     * Clear user cache by email.
     */
    protected function clearUserCacheByEmail(string $email): void
    {
        Cache::forget($this->cachePrefix . 'email:' . $email);
    }

    /**
     * Clear user cache by employee ID.
     */
    protected function clearUserCacheByEmployeeId(string $employeeId): void
    {
        Cache::forget($this->cachePrefix . 'employee_id:' . $employeeId);
    }

    /**
     * Clear all user-related cache.
     */
    protected function clearCache(): void
    {
        Cache::tags(['users'])->flush();
    }
} 