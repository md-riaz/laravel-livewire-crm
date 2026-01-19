<?php

namespace App\Pipelines\LeadPipeline;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validate Lead Data Pipeline Stage
 *
 * Validates incoming lead data against business rules and constraints.
 * Throws validation exceptions for invalid data.
 */
class ValidateLeadData
{
    /**
     * Handle the lead data validation
     *
     * @param array<string, mixed> $data
     * @param Closure $next
     * @return mixed
     * @throws ValidationException
     */
    public function handle(array $data, Closure $next): mixed
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'score' => 'nullable|in:' . implode(',', config('crm.leads.valid_scores', [])),
            'estimated_value' => 'nullable|numeric|min:0',
        ];

        // Add strict validation if configured
        if (config('crm.pipeline.strict_validation', true)) {
            $rules['email'] = 'required|email|max:255';
            $rules['phone'] = 'required|string|max:50';
        }

        // Require assignment if configured
        if (config('crm.leads.require_assignment', false)) {
            $rules['assigned_to_user_id'] = 'required|integer|exists:users,id';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $next($data);
    }
}
