<?php

namespace App\Http\Requests;

use App\DTOs\CallWrapUpDTO;
use App\Models\Call;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for wrapping up a call
 *
 * Validates call wrap-up data after call completion.
 */
class WrapUpCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $call = $this->route('call');
        
        if (!$call instanceof Call) {
            return false;
        }

        // Verify tenant ownership
        if ($call->tenant_id !== $this->user()->tenant_id) {
            return false;
        }

        // Verify user is the call owner or has permission
        return $call->user_id === $this->user()->id 
            || $this->user()->can('wrapup', $call);
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
            'disposition_id' => [
                'nullable',
                'integer',
                'exists:call_dispositions,id,tenant_id,' . $tenantId,
            ],
            'wrapup_notes' => ['nullable', 'string', 'max:5000'],
            'related_id' => ['nullable', 'integer'],
            'related_type' => ['nullable', 'string', 'in:lead'],
            'follow_up_actions' => ['nullable', 'array'],
            'follow_up_actions.*' => ['string', 'max:500'],
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
            'disposition_id' => 'call disposition',
            'wrapup_notes' => 'wrap-up notes',
            'related_id' => 'related record',
            'related_type' => 'related record type',
            'follow_up_actions' => 'follow-up actions',
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
            'wrapup_notes.max' => 'Wrap-up notes cannot exceed 5000 characters.',
            'disposition_id.exists' => 'The selected disposition is not valid for your organization.',
            'related_type.in' => 'The related record type must be a lead.',
        ];
    }

    /**
     * Convert validated data to CallWrapUpDTO
     */
    public function toDTO(): CallWrapUpDTO
    {
        $call = $this->route('call');
        
        return CallWrapUpDTO::fromArray([
            'call_id' => $call->id,
            'disposition_id' => $this->input('disposition_id'),
            'wrapup_notes' => $this->input('wrapup_notes'),
            'related_id' => $this->input('related_id'),
            'related_type' => $this->input('related_type'),
            'follow_up_actions' => $this->input('follow_up_actions'),
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Validate that related_id exists if related_type is provided
        if ($this->has('related_type') && $this->input('related_type') === 'lead') {
            if ($this->has('related_id')) {
                $this->merge([
                    'related_id' => (int) $this->input('related_id'),
                ]);
            }
        }
    }
}
