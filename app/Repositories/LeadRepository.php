<?php

namespace App\Repositories;

use App\Contracts\LeadRepositoryInterface;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

/**
 * Lead Repository
 *
 * Handles complex query logic and data access patterns for leads.
 * Implements repository pattern for better testability and separation of concerns.
 */
readonly class LeadRepository implements LeadRepositoryInterface
{
    /**
     * Find a lead by ID
     */
    public function find(int $id): ?Lead
    {
        return Lead::find($id);
    }

    /**
     * Find a lead by ID with relationships loaded
     */
    public function findWithRelations(int $id, array $relations = []): ?Lead
    {
        $query = Lead::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Get leads by status
     */
    public function getByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator
    {
        return Lead::query()
            ->where('lead_status_id', $statusId)
            ->with(['assignedTo', 'status'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get leads assigned to a user
     */
    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Lead::query()
            ->where('assigned_to_user_id', $userId)
            ->with(['status', 'createdBy'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Search leads with advanced filters
     */
    public function search(array $filters): Collection
    {
        $query = Lead::query()->with(['assignedTo', 'status']);

        // Filter by status
        if (!empty($filters['status_id'])) {
            $query->where('lead_status_id', $filters['status_id']);
        }

        // Filter by assigned user
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to_user_id', $filters['assigned_to']);
        }

        // Filter by score
        if (!empty($filters['score'])) {
            $query->where('score', $filters['score']);
        }

        // Filter by source
        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        // Search by name, email, or company
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Filter by estimated value range
        if (!empty($filters['min_value'])) {
            $query->where('estimated_value', '>=', $filters['min_value']);
        }

        if (!empty($filters['max_value'])) {
            $query->where('estimated_value', '<=', $filters['max_value']);
        }

        // Filter by date range
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query->get();
    }

    /**
     * Get leads requiring follow-up
     */
    public function getRequiringFollowUp(?int $userId = null): Collection
    {
        $query = Lead::query()
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<=', Carbon::now())
            ->with(['assignedTo', 'status']);

        if ($userId !== null) {
            $query->where('assigned_to_user_id', $userId);
        }

        return $query->orderBy('next_followup_at')->get();
    }

    /**
     * Get leads by score
     */
    public function getByScore(string $score): Collection
    {
        return Lead::query()
            ->where('score', $score)
            ->with(['assignedTo', 'status'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Create a new lead
     */
    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    /**
     * Update a lead
     */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);
        return $lead->fresh();
    }
}
