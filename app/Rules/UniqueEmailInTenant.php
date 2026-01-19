<?php

namespace App\Rules;

use App\Models\Lead;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that an email is unique within the tenant
 *
 * Ensures email uniqueness scoped to tenant for multi-tenancy.
 */
class UniqueEmailInTenant implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private ?int $tenantId = null,
        private ?int $ignoreLeadId = null
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
        if (empty($value)) {
            return; // Allow null/empty emails
        }

        $tenantId = $this->tenantId ?? Auth::user()?->tenant_id;

        if (!$tenantId) {
            $fail('The :attribute cannot be validated without a tenant context.');
            return;
        }

        $query = Lead::where('tenant_id', $tenantId)
            ->where('email', $value);

        // Ignore current lead if updating
        if ($this->ignoreLeadId) {
            $query->where('id', '!=', $this->ignoreLeadId);
        }

        if ($query->exists()) {
            $fail('The :attribute already exists in your organization.');
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute is already associated with another lead in your organization.';
    }
}
