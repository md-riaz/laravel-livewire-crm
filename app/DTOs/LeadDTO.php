<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Lead Data Transfer Object
 *
 * Immutable DTO for type-safe lead data transfer between layers.
 * Provides validation rules and transformation methods.
 */
readonly class LeadDTO
{
    public function __construct(
        public string $name,
        public ?string $company_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $source = null,
        public string $score = 'warm',
        public ?float $estimated_value = null,
        public ?int $assigned_to_user_id = null,
        public ?int $lead_status_id = null,
        public ?int $created_by_user_id = null,
        public ?int $tenant_id = null,
    ) {
        $this->validateScore($score);
        $this->validateEstimatedValue($estimated_value);
    }

    /**
     * Create DTO from array data
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        // Validate data structure
        $validator = Validator::make($data, static::validate());
        
        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid lead data: ' . $validator->errors()->first()
            );
        }

        return new self(
            name: $data['name'],
            company_name: $data['company_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            source: $data['source'] ?? null,
            score: $data['score'] ?? 'warm',
            estimated_value: isset($data['estimated_value']) ? (float) $data['estimated_value'] : null,
            assigned_to_user_id: isset($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null,
            lead_status_id: isset($data['lead_status_id']) ? (int) $data['lead_status_id'] : null,
            created_by_user_id: isset($data['created_by_user_id']) ? (int) $data['created_by_user_id'] : null,
            tenant_id: isset($data['tenant_id']) ? (int) $data['tenant_id'] : null,
        );
    }

    /**
     * Create DTO from HTTP request
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return self::fromArray([
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'source' => $request->input('source'),
            'score' => $request->input('score', 'warm'),
            'estimated_value' => $request->input('estimated_value'),
            'assigned_to_user_id' => $request->input('assigned_to_user_id'),
            'lead_status_id' => $request->input('lead_status_id'),
            'created_by_user_id' => $request->user()?->id,
            'tenant_id' => $request->user()?->tenant_id,
        ]);
    }

    /**
     * Convert DTO to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'score' => $this->score,
            'estimated_value' => $this->estimated_value,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'lead_status_id' => $this->lead_status_id,
            'created_by_user_id' => $this->created_by_user_id,
            'tenant_id' => $this->tenant_id,
        ], fn($value) => $value !== null);
    }

    /**
     * Get validation rules for lead data
     *
     * @return array<string, mixed>
     */
    public static function validate(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:255'],
            'score' => ['required', 'in:hot,warm,cold'],
            'estimated_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lead_status_id' => ['nullable', 'integer', 'exists:lead_statuses,id'],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ];
    }

    /**
     * Create a new DTO with updated values
     *
     * @param array<string, mixed> $updates
     * @return self
     */
    public function with(array $updates): self
    {
        return new self(
            name: $updates['name'] ?? $this->name,
            company_name: $updates['company_name'] ?? $this->company_name,
            email: $updates['email'] ?? $this->email,
            phone: $updates['phone'] ?? $this->phone,
            source: $updates['source'] ?? $this->source,
            score: $updates['score'] ?? $this->score,
            estimated_value: $updates['estimated_value'] ?? $this->estimated_value,
            assigned_to_user_id: $updates['assigned_to_user_id'] ?? $this->assigned_to_user_id,
            lead_status_id: $updates['lead_status_id'] ?? $this->lead_status_id,
            created_by_user_id: $updates['created_by_user_id'] ?? $this->created_by_user_id,
            tenant_id: $updates['tenant_id'] ?? $this->tenant_id,
        );
    }

    /**
     * Validate score value
     *
     * @throws InvalidArgumentException
     */
    private function validateScore(string $score): void
    {
        if (!in_array($score, ['hot', 'warm', 'cold'])) {
            throw new InvalidArgumentException("Invalid score: {$score}. Must be one of: hot, warm, cold");
        }
    }

    /**
     * Validate estimated value
     *
     * @throws InvalidArgumentException
     */
    private function validateEstimatedValue(?float $value): void
    {
        if ($value !== null && ($value < 0 || $value > 999999999.99)) {
            throw new InvalidArgumentException("Estimated value must be between 0 and 999999999.99");
        }
    }
}
