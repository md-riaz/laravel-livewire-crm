<?php

namespace App\Actions;

use App\Contracts\LeadServiceInterface;
use App\Models\Lead;

/**
 * Assign Lead Action
 *
 * Single-purpose action for assigning a lead to a user.
 * Handles assignment logic and related notifications.
 */
readonly class AssignLeadAction
{
    public function __construct(
        private LeadServiceInterface $leadService
    ) {}

    /**
     * Execute the action to assign a lead to a user
     *
     * @param Lead $lead Lead to assign
     * @param int $userId User ID to assign to
     * @return Lead Updated lead model
     */
    public function execute(Lead $lead, int $userId): Lead
    {
        return $this->leadService->assignLead($lead, $userId);
    }
}
