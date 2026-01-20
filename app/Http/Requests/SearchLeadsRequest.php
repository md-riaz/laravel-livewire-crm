<?php

namespace App\Http\Requests;

use App\DTOs\LeadSearchDTO;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for searching leads
 *
 * Validates and structures lead search parameters.
 */
class SearchLeadsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('viewAny', \App\Models\Lead::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'search' => ['nullable', 'string', 'max:255'],
            'lead_status_id' => [
                'nullable',
                'integer',
                'exists:lead_statuses,id,tenant_id,' . $tenantId,
            ],
            'assigned_to_user_id' => [
                'nullable',
                'integer',
                'exists:users,id,tenant_id,' . $tenantId,
            ],
            'score' => ['nullable', 'in:hot,warm,cold'],
            'source' => ['nullable', 'string', 'max:255'],
            'min_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'max_estimated_value' => ['nullable', 'numeric', 'min:0', 'gte:min_estimated_value'],
            'sort_by' => ['nullable', 'in:created_at,updated_at,name,estimated_value,last_contacted_at'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'search query',
            'lead_status_id' => 'lead status',
            'assigned_to_user_id' => 'assigned user',
            'score' => 'lead score',
            'source' => 'lead source',
            'min_estimated_value' => 'minimum estimated value',
            'max_estimated_value' => 'maximum estimated value',
            'sort_by' => 'sort field',
            'sort_direction' => 'sort direction',
            'per_page' => 'results per page',
            'page' => 'page number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'max_estimated_value.gte' => 'Maximum estimated value must be greater than or equal to minimum value.',
            'per_page.max' => 'Cannot display more than 100 results per page.',
            'sort_by.in' => 'Invalid sort field specified.',
        ];
    }

    /**
     * Convert validated data to LeadSearchDTO
     */
    public function toDTO(): LeadSearchDTO
    {
        return LeadSearchDTO::fromArray([
            'search' => $this->input('search'),
            'lead_status_id' => $this->input('lead_status_id'),
            'assigned_to_user_id' => $this->input('assigned_to_user_id'),
            'score' => $this->input('score'),
            'source' => $this->input('source'),
            'min_estimated_value' => $this->input('min_estimated_value'),
            'max_estimated_value' => $this->input('max_estimated_value'),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
            'tenant_id' => $this->user()->tenant_id,
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        $this->merge([
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
        ]);
    }
}
