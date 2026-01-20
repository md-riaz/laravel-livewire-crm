<?php

namespace App\Contracts;

use App\Models\Call;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Call Repository Interface
 *
 * Defines the contract for call data access operations.
 * Handles call-specific query logic and statistics.
 */
interface CallRepositoryInterface
{
    /**
     * Find a call by ID
     *
     * @param int $id Call ID
     * @return Call|null
     */
    public function find(int $id): ?Call;

    /**
     * Get calls for a specific entity
     *
     * @param string $relatedType Entity type
     * @param int $relatedId Entity ID
     * @return Collection<int, Call>
     */
    public function getForEntity(string $relatedType, int $relatedId): Collection;

    /**
     * Get calls for a user within a date range
     *
     * @param int $userId User ID
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection<int, Call>
     */
    public function getForUserInRange(int $userId, Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get active calls for a user
     *
     * @param int $userId User ID
     * @return Collection<int, Call>
     */
    public function getActiveCalls(int $userId): Collection;

    /**
     * Get calls requiring wrap-up
     *
     * @param int|null $userId Optional user ID to filter by
     * @return Collection<int, Call>
     */
    public function getRequiringWrapUp(?int $userId = null): Collection;

    /**
     * Create a new call
     *
     * @param array<string, mixed> $data Call data
     * @return Call
     */
    public function create(array $data): Call;

    /**
     * Update a call
     *
     * @param Call $call Call to update
     * @param array<string, mixed> $data Updated data
     * @return Call
     */
    public function update(Call $call, array $data): Call;
}
