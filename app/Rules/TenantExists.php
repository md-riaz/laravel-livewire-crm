<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Validates that a tenant_id belongs to the current authenticated tenant
 *
 * Ensures multi-tenant data isolation by verifying tenant ownership.
 */
class TenantExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        if (!$user) {
            $fail('The :attribute must belong to an authenticated user.');
            return;
        }

        // Check if the tenant exists
        if (!Tenant::where('id', $value)->exists()) {
            $fail('The selected :attribute is invalid.');
            return;
        }

        // Verify the tenant belongs to the current user
        if ($user->tenant_id != $value) {
            $fail('The :attribute does not belong to your organization.');
            return;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute is invalid or does not belong to your organization.';
    }
}
