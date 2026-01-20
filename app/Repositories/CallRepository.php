<?php

namespace App\Repositories;

use App\Contracts\CallRepositoryInterface;
use App\Models\Call;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Call Repository
 *
 * Handles call-specific query logic and statistics.
 * Implements repository pattern for call data access.
 */
readonly class CallRepository implements CallRepositoryInterface
{
    /**
     * Find a call by ID
     */
    public function find(int $id): ?Call
    {
        return Call::find($id);
    }

    /**
     * Get calls for a specific entity
     */
    public function getForEntity(string $relatedType, int $relatedId): Collection
    {
        return Call::query()
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->with(['user', 'disposition'])
            ->latest('started_at')
            ->get();
    }

    /**
     * Get calls for a user within a date range
     */
    public function getForUserInRange(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return Call::query()
            ->where('user_id', $userId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->with(['disposition', 'related'])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Get active calls for a user
     */
    public function getActiveCalls(int $userId): Collection
    {
        return Call::query()
            ->where('user_id', $userId)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->with(['related'])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Get calls requiring wrap-up
     */
    public function getRequiringWrapUp(?int $userId = null): Collection
    {
        $query = Call::query()
            ->whereNotNull('ended_at')
            ->whereNull('disposition_id')
            ->with(['user', 'related']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('ended_at', 'desc')->get();
    }

    /**
     * Create a new call
     */
    public function create(array $data): Call
    {
        return Call::create($data);
    }

    /**
     * Update a call
     */
    public function update(Call $call, array $data): Call
    {
        $call->update($data);
        return $call->fresh();
    }
}
