<?php

namespace App\Actions;

use App\Contracts\LeadServiceInterface;
use App\Models\Lead;

/**
 * Move Lead Action
 *
 * Single-purpose action for moving a lead to a different status.
 * Handles status transitions and related business logic.
 */
readonly class MoveLeadAction
{
    public function __construct(
        private LeadServiceInterface $leadService
    ) {}

    /**
     * Execute the action to move a lead to a new status
     *
     * @param Lead $lead Lead to move
     * @param int $newStatusId New status ID
     * @param string|null $note Optional note about the status change
     * @return Lead Updated lead model
     */
    public function execute(Lead $lead, int $newStatusId, ?string $note = null): Lead
    {
        return $this->leadService->moveLead($lead, $newStatusId, $note);
    }
}
