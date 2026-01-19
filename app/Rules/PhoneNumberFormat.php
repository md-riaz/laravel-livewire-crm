<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates phone number format
 *
 * Supports multiple international formats and extensions.
 */
class PhoneNumberFormat implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private bool $allowExtensions = true
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
        if (!is_string($value)) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        // Remove common formatting characters
        $cleaned = preg_replace('/[\s\-\(\)\.]/', '', $value);

        // Check for extension separator if extensions are allowed
        if ($this->allowExtensions) {
            $cleaned = preg_replace('/[xX][\d]+$/', '', $cleaned);
        }

        // Must contain only digits, plus sign (at start), or common separators
        if (!preg_match('/^\+?[\d]{7,20}$/', $cleaned)) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        // Check minimum length (international minimum is typically 7 digits)
        if (strlen($cleaned) < 7) {
            $fail('The :attribute must be at least 7 digits.');
            return;
        }

        // Check maximum length
        if (strlen($cleaned) > 20) {
            $fail('The :attribute cannot exceed 20 digits.');
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute must be a valid phone number format.';
    }
}
