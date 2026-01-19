<?php

namespace App\Contracts;

use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lead Repository Interface
 *
 * Defines the contract for lead data access operations.
 * Handles complex query logic and data retrieval patterns.
 */
interface LeadRepositoryInterface
{
    /**
     * Find a lead by ID
     *
     * @param int $id Lead ID
     * @return Lead|null
     */
    public function find(int $id): ?Lead;

    /**
     * Find a lead by ID with relationships loaded
     *
     * @param int $id Lead ID
     * @param array<int, string> $relations Relations to eager load
     * @return Lead|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Lead;

    /**
     * Get leads by status
     *
     * @param int $statusId Status ID
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads assigned to a user
     *
     * @param int $userId User ID
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search leads with advanced filters
     *
     * @param array<string, mixed> $filters Search filters
     * @return Collection<int, Lead>
     */
    public function search(array $filters): Collection;

    /**
     * Get leads requiring follow-up
     *
     * @param int|null $userId Optional user ID to filter by
     * @return Collection<int, Lead>
     */
    public function getRequiringFollowUp(?int $userId = null): Collection;

    /**
     * Get leads by score
     *
     * @param string $score Score value (hot, warm, cold)
     * @return Collection<int, Lead>
     */
    public function getByScore(string $score): Collection;

    /**
     * Create a new lead
     *
     * @param array<string, mixed> $data Lead data
     * @return Lead
     */
    public function create(array $data): Lead;

    /**
     * Update a lead
     *
     * @param Lead $lead Lead to update
     * @param array<string, mixed> $data Updated data
     * @return Lead
     */
    public function update(Lead $lead, array $data): Lead;
}
