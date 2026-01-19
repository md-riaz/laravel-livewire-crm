<?php

namespace App\Actions;

use App\Contracts\LeadServiceInterface;
use App\DTOs\LeadDTO;
use App\Models\Lead;

/**
 * Update Lead Action
 *
 * Single-purpose action for updating an existing lead.
 * Handles partial updates with DTO validation.
 */
readonly class UpdateLeadAction
{
    public function __construct(
        private LeadServiceInterface $leadService
    ) {}

    /**
     * Execute the action to update a lead
     *
     * @param Lead $lead Lead to update
     * @param LeadDTO $dto Updated lead data
     * @return Lead Updated lead model
     */
    public function execute(Lead $lead, LeadDTO $dto): Lead
    {
        return $this->leadService->updateLead($lead, $dto->toArray());
    }
}
