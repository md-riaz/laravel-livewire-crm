<?php

namespace App\Services;

use App\Contracts\LeadServiceInterface;
use App\Contracts\LeadRepositoryInterface;
use App\Contracts\ActivityLoggerInterface;
use App\Events\LeadAssigned;
use App\Events\LeadCreated;
use App\Events\LeadStatusChanged;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;

/**
 * Lead Service
 *
 * Handles lead management operations with business logic.
 * Uses repository pattern for data access and events for side effects.
 */
readonly class LeadService implements LeadServiceInterface
{
    public function __construct(
        private LeadRepositoryInterface $leadRepository,
        private ActivityLoggerInterface $activityLogger
    ) {}

    /**
     * Create a new lead with the given data
     * 
     * Runs the lead through a pipeline for validation, enrichment,
     * and default assignments before persisting.
     */
    public function createLead(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            // Run through pipeline for data processing
            $processedData = app(Pipeline::class)
                ->send($data)
                ->through([
                    \App\Pipelines\LeadPipeline\ValidateLeadData::class,
                    \App\Pipelines\LeadPipeline\EnrichLeadData::class,
                    \App\Pipelines\LeadPipeline\AssignDefaultStatus::class,
                ])
                ->thenReturn();

            // Create the lead
            $lead = $this->leadRepository->create($processedData);

            // Log activity if enabled
            if (config('crm.leads.auto_create_activities', true)) {
                $this->activityLogger->logForLead(
                    $lead,
                    'created',
                    ['source' => $data['source'] ?? 'manual']
                );
            }

            // Dispatch event
            event(new LeadCreated($lead));

            return $lead;
        });
    }

    /**
     * Update an existing lead
     */
    public function updateLead(Lead $lead, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $data) {
            $original = $lead->replicate();
            $updated = $this->leadRepository->update($lead, $data);

            // Log significant changes
            if (config('crm.leads.auto_create_activities', true)) {
                $changes = $this->getSignificantChanges($original, $updated);
                if (!empty($changes)) {
                    $this->activityLogger->logForLead(
                        $updated,
                        'updated',
                        ['changes' => $changes]
                    );
                }
            }

            return $updated;
        });
    }

    /**
     * Assign a lead to a user
     */
    public function assignLead(Lead $lead, int $userId): Lead
    {
        return DB::transaction(function () use ($lead, $userId) {
            $previousUserId = $lead->assigned_to_user_id;
            
            $updated = $this->leadRepository->update($lead, [
                'assigned_to_user_id' => $userId,
            ]);

            // Log the assignment
            $this->activityLogger->logForLead(
                $updated,
                'assigned',
                [
                    'previous_user_id' => $previousUserId,
                    'new_user_id' => $userId,
                ]
            );

            // Dispatch event for notifications
            event(new LeadAssigned($updated, $previousUserId, $userId));

            return $updated;
        });
    }

    /**
     * Move a lead to a different status
     */
    public function moveLead(Lead $lead, int $newStatusId, ?string $note = null): Lead
    {
        return DB::transaction(function () use ($lead, $newStatusId, $note) {
            $previousStatusId = $lead->lead_status_id;

            $updated = $this->leadRepository->update($lead, [
                'lead_status_id' => $newStatusId,
            ]);

            // Log the status change
            $this->activityLogger->logForLead(
                $updated,
                'status_changed',
                [
                    'previous_status_id' => $previousStatusId,
                    'new_status_id' => $newStatusId,
                    'note' => $note,
                ]
            );

            // Dispatch event
            event(new LeadStatusChanged($updated, $previousStatusId, $newStatusId));

            return $updated;
        });
    }

    /**
     * Search leads based on filters
     */
    public function searchLeads(array $filters): Collection
    {
        return $this->leadRepository->search($filters);
    }

    /**
     * Get significant changes between two lead instances
     * 
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function getSignificantChanges(Lead $original, Lead $updated): array
    {
        $trackedFields = ['name', 'email', 'phone', 'company_name', 'score', 'estimated_value'];
        $changes = [];

        foreach ($trackedFields as $field) {
            if ($original->$field !== $updated->$field) {
                $changes[$field] = [
                    'old' => $original->$field,
                    'new' => $updated->$field,
                ];
            }
        }

        return $changes;
    }
}
