<?php

namespace App\Actions;

use App\Contracts\LeadServiceInterface;
use App\DTOs\LeadDTO;
use App\Models\Lead;

/**
 * Create Lead Action
 *
 * Single-purpose action for creating a new lead.
 * Encapsulates lead creation logic with proper dependency injection.
 */
readonly class CreateLeadAction
{
    public function __construct(
        private LeadServiceInterface $leadService
    ) {}

    /**
     * Execute the action to create a new lead
     *
     * @param LeadDTO $dto Lead data transfer object
     * @return Lead Created lead model
     */
    public function execute(LeadDTO $dto): Lead
    {
        return $this->leadService->createLead($dto->toArray());
    }
}
