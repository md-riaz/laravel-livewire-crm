<?php

namespace App\Contracts;

use App\Models\Lead;
use Illuminate\Support\Collection;

/**
 * Lead Service Interface
 *
 * Defines the contract for lead management operations including
 * creation, updates, assignments, and lead movement through pipeline.
 */
interface LeadServiceInterface
{
    /**
     * Create a new lead with the given data
     *
     * @param array<string, mixed> $data Lead data
     * @return Lead
     */
    public function createLead(array $data): Lead;

    /**
     * Update an existing lead
     *
     * @param Lead $lead Lead to update
     * @param array<string, mixed> $data Updated data
     * @return Lead
     */
    public function updateLead(Lead $lead, array $data): Lead;

    /**
     * Assign a lead to a user
     *
     * @param Lead $lead Lead to assign
     * @param int $userId User ID to assign to
     * @return Lead
     */
    public function assignLead(Lead $lead, int $userId): Lead;

    /**
     * Move a lead to a different status
     *
     * @param Lead $lead Lead to move
     * @param int $newStatusId New status ID
     * @param string|null $note Optional note about the status change
     * @return Lead
     */
    public function moveLead(Lead $lead, int $newStatusId, ?string $note = null): Lead;

    /**
     * Search leads based on filters
     *
     * @param array<string, mixed> $filters Search filters
     * @return Collection<int, Lead>
     */
    public function searchLeads(array $filters): Collection;
}
