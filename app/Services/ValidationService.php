<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validation Service
 *
 * Centralized business rule validation for complex cross-entity validation.
 * Provides tenant-aware validation with custom error messages.
 */
class ValidationService
{
    /**
     * Validate lead assignment to user
     *
     * @param int $leadId
     * @param int $userId
     * @param int $tenantId
     * @throws ValidationException
     */
    public function validateLeadAssignment(int $leadId, int $userId, int $tenantId): void
    {
        $lead = Lead::where('id', $leadId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$lead) {
            throw ValidationException::withMessages([
                'lead_id' => ['The lead does not exist or does not belong to your organization.'],
            ]);
        }

        $user = User::where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['The user does not exist or does not belong to your organization.'],
            ]);
        }

        // Check if user has permission to be assigned leads
        if (!$user->can('be-assigned-leads')) {
            throw ValidationException::withMessages([
                'user_id' => ['This user cannot be assigned leads.'],
            ]);
        }
    }

    /**
     * Validate lead status change
     *
     * @param int $leadId
     * @param int $newStatusId
     * @param int $tenantId
     * @throws ValidationException
     */
    public function validateLeadStatusChange(int $leadId, int $newStatusId, int $tenantId): void
    {
        $lead = Lead::where('id', $leadId)
            ->where('tenant_id', $tenantId)
            ->with('status')
            ->first();

        if (!$lead) {
            throw ValidationException::withMessages([
                'lead_id' => ['The lead does not exist or does not belong to your organization.'],
            ]);
        }

        $newStatus = LeadStatus::where('id', $newStatusId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$newStatus) {
            throw ValidationException::withMessages([
                'status_id' => ['The status does not exist or does not belong to your organization.'],
            ]);
        }

        // Check if status transition is allowed
        if (!$this->isStatusTransitionAllowed($lead->lead_status_id, $newStatusId)) {
            throw ValidationException::withMessages([
                'status_id' => ['This status transition is not allowed.'],
            ]);
        }
    }

    /**
     * Validate business rules for lead creation
     *
     * @param array<string, mixed> $data
     * @param int $tenantId
     * @throws ValidationException
     */
    public function validateLeadCreation(array $data, int $tenantId): void
    {
        // Check if email is already used within tenant
        if (!empty($data['email'])) {
            $exists = Lead::where('tenant_id', $tenantId)
                ->where('email', $data['email'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => ['A lead with this email already exists in your organization.'],
                ]);
            }
        }

        // Validate estimated value based on lead score
        if (isset($data['estimated_value']) && isset($data['score'])) {
            $this->validateEstimatedValueForScore($data['estimated_value'], $data['score']);
        }

        // Validate required fields based on lead source
        if (isset($data['source'])) {
            $this->validateRequiredFieldsBySource($data);
        }
    }

    /**
     * Validate bulk operations
     *
     * @param array<int> $leadIds
     * @param int $tenantId
     * @return array<int, Lead>
     * @throws ValidationException
     */
    public function validateBulkLeadOperation(array $leadIds, int $tenantId): array
    {
        if (empty($leadIds)) {
            throw ValidationException::withMessages([
                'leads' => ['No leads selected for operation.'],
            ]);
        }

        if (count($leadIds) > 100) {
            throw ValidationException::withMessages([
                'leads' => ['Cannot perform bulk operation on more than 100 leads at once.'],
            ]);
        }

        $leads = Lead::where('tenant_id', $tenantId)
            ->whereIn('id', $leadIds)
            ->get();

        if ($leads->count() !== count($leadIds)) {
            throw ValidationException::withMessages([
                'leads' => ['Some leads do not exist or do not belong to your organization.'],
            ]);
        }

        return $leads->keyBy('id')->toArray();
    }

    /**
     * Validate tenant-specific business rules
     *
     * @param string $rule
     * @param mixed $value
     * @param int $tenantId
     * @return bool
     */
    public function validateTenantRule(string $rule, mixed $value, int $tenantId): bool
    {
        // Define rule constants for type safety
        return match($rule) {
            'max_leads_per_user' => $this->validateMaxLeadsPerUser($value, $tenantId),
            'required_lead_fields' => $this->validateRequiredLeadFields($value, $tenantId),
            default => throw new \InvalidArgumentException("Unknown validation rule: {$rule}"),
        };
    }

    /**
     * Check if status transition is allowed
     */
    private function isStatusTransitionAllowed(int $currentStatusId, int $newStatusId): bool
    {
        // Implement status transition logic
        // This is a simple implementation - extend based on business rules
        
        if ($currentStatusId === $newStatusId) {
            return false; // Cannot transition to same status
        }

        // Add more complex transition rules here based on business requirements
        return true;
    }

    /**
     * Validate estimated value against lead score
     *
     * @throws ValidationException
     */
    private function validateEstimatedValueForScore(float $estimatedValue, string $score): void
    {
        $minValues = [
            'hot' => 10000,
            'warm' => 1000,
            'cold' => 0,
        ];

        if (isset($minValues[$score]) && $estimatedValue < $minValues[$score]) {
            throw ValidationException::withMessages([
                'estimated_value' => [
                    "For {$score} leads, estimated value should be at least \${$minValues[$score]}."
                ],
            ]);
        }
    }

    /**
     * Validate required fields by source
     *
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    private function validateRequiredFieldsBySource(array $data): void
    {
        $requiredFields = match($data['source']) {
            'website' => ['email'],
            'phone' => ['phone'],
            'referral' => ['company_name'],
            default => [],
        };

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $fieldName = ucfirst(str_replace('_', ' ', $field));
                throw ValidationException::withMessages([
                    $field => ["{$fieldName} is required for {$data['source']} leads."],
                ]);
            }
        }
    }

    /**
     * Validate max leads per user
     */
    private function validateMaxLeadsPerUser(int $userId, int $tenantId): bool
    {
        // Implement max leads per user logic
        $maxLeads = 100; // This should come from tenant configuration
        
        $currentLeads = Lead::where('tenant_id', $tenantId)
            ->where('assigned_to_user_id', $userId)
            ->count();

        return $currentLeads < $maxLeads;
    }

    /**
     * Validate required lead fields
     *
     * @param array<string, mixed> $data
     */
    private function validateRequiredLeadFields(array $data, int $tenantId): bool
    {
        // This should load tenant-specific required fields
        // For now, just validate basic required fields
        $requiredFields = ['name', 'score'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }
}
