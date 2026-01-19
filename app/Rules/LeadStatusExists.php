<?php

namespace App\Rules;

use App\Models\LeadStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that a lead status exists for the current tenant
 *
 * Ensures status belongs to the tenant's configured statuses.
 */
class LeadStatusExists implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private ?int $tenantId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenantId = $this->tenantId ?? Auth::user()?->tenant_id;

        if (!$tenantId) {
            $fail('The :attribute cannot be validated without a tenant context.');
            return;
        }

        $exists = LeadStatus::where('id', $value)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$exists) {
            $fail('The selected :attribute is not valid for your organization.');
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The selected :attribute does not exist for your organization.';
    }
}
