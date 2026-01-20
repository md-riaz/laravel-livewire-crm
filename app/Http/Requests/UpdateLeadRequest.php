<?php

namespace App\Http\Requests;

use App\DTOs\LeadDTO;
use App\Models\Lead;
use App\Rules\LeadStatusExists;
use App\Rules\PhoneNumberFormat;
use App\Rules\UniqueEmailInTenant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing lead
 *
 * Validates lead update data with tenant-aware rules.
 */
class UpdateLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        
        if (!$lead instanceof Lead) {
            return false;
        }

        // Verify tenant ownership
        if ($lead->tenant_id !== $this->user()->tenant_id) {
            return false;
        }

        return $this->user()->can('update', $lead);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        $lead = $this->route('lead');
        $leadId = $lead instanceof Lead ? $lead->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                new UniqueEmailInTenant($tenantId, $leadId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                new PhoneNumberFormat(),
            ],
            'source' => ['nullable', 'string', 'max:255'],
            'score' => ['sometimes', 'required', 'in:hot,warm,cold'],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'assigned_to_user_id' => [
                'nullable',
                'integer',
                'exists:users,id,tenant_id,' . $tenantId,
            ],
            'lead_status_id' => [
                'nullable',
                'integer',
                new LeadStatusExists($tenantId),
            ],
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
            'name' => 'lead name',
            'company_name' => 'company name',
            'email' => 'email address',
            'phone' => 'phone number',
            'source' => 'lead source',
            'score' => 'lead score',
            'estimated_value' => 'estimated value',
            'assigned_to_user_id' => 'assigned user',
            'lead_status_id' => 'lead status',
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
            'name.required' => 'A lead name is required.',
            'email.email' => 'Please provide a valid email address.',
            'score.in' => 'Lead score must be hot, warm, or cold.',
            'estimated_value.numeric' => 'Estimated value must be a number.',
            'estimated_value.min' => 'Estimated value cannot be negative.',
        ];
    }

    /**
     * Convert validated data to LeadDTO
     */
    public function toDTO(): LeadDTO
    {
        $lead = $this->route('lead');
        
        return LeadDTO::fromArray([
            'name' => $this->input('name', $lead->name),
            'company_name' => $this->input('company_name', $lead->company_name),
            'email' => $this->input('email', $lead->email),
            'phone' => $this->input('phone', $lead->phone),
            'source' => $this->input('source', $lead->source),
            'score' => $this->input('score', $lead->score),
            'estimated_value' => $this->input('estimated_value', $lead->estimated_value),
            'assigned_to_user_id' => $this->input('assigned_to_user_id', $lead->assigned_to_user_id),
            'lead_status_id' => $this->input('lead_status_id', $lead->lead_status_id),
            'tenant_id' => $lead->tenant_id,
            'created_by_user_id' => $lead->created_by_user_id,
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Prevent tenant_id modification
        if ($this->has('tenant_id')) {
            $this->offsetUnset('tenant_id');
        }
    }
}
