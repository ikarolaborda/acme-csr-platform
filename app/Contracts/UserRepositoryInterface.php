<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * Find a user by ID.
     */
    public function find(int $id): ?User;

    /**
     * Find a user by employee ID.
     */
    public function findByEmployeeId(string $employeeId): ?User;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get all users with pagination.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get users by role.
     */
    public function getByRole(string $role): Collection;

    /**
     * Get active users.
     */
    public function getActive(): Collection;

    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Update a user.
     */
    public function update(int $id, array $data): User;

    /**
     * Delete a user.
     */
    public function delete(int $id): bool;

    /**
     * Get top donors.
     */
    public function getTopDonors(int $limit = 10): Collection;

    /**
     * Search users.
     */
    public function search(string $query): Collection;
} 