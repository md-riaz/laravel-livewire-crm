<?php

namespace App\DTOs\Responses;

use App\Models\Lead;
use Carbon\Carbon;

/**
 * Lead Resource DTO
 *
 * Response DTO for single lead representation.
 * Provides consistent API response structure.
 */
readonly class LeadResource
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $company_name,
        public ?string $email,
        public ?string $phone,
        public ?string $source,
        public string $score,
        public ?float $estimated_value,
        public ?int $assigned_to_user_id,
        public ?string $assigned_to_user_name,
        public int $lead_status_id,
        public string $lead_status_name,
        public ?string $lead_status_color,
        public int $tenant_id,
        public ?Carbon $last_contacted_at,
        public ?Carbon $next_followup_at,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Create resource from Lead model
     *
     * @param Lead $lead
     * @return self
     */
    public static function fromModel(Lead $lead): self
    {
        return new self(
            id: $lead->id,
            name: $lead->name,
            company_name: $lead->company_name,
            email: $lead->email,
            phone: $lead->phone,
            source: $lead->source,
            score: $lead->score,
            estimated_value: $lead->estimated_value ? (float) $lead->estimated_value : null,
            assigned_to_user_id: $lead->assigned_to_user_id,
            assigned_to_user_name: $lead->assignedTo?->name,
            lead_status_id: $lead->lead_status_id,
            lead_status_name: $lead->status?->name ?? 'Unknown',
            lead_status_color: $lead->status?->color,
            tenant_id: $lead->tenant_id,
            last_contacted_at: $lead->last_contacted_at,
            next_followup_at: $lead->next_followup_at,
            created_at: $lead->created_at,
            updated_at: $lead->updated_at,
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'score' => $this->score,
            'estimated_value' => $this->estimated_value,
            'assigned_to_user' => [
                'id' => $this->assigned_to_user_id,
                'name' => $this->assigned_to_user_name,
            ],
            'status' => [
                'id' => $this->lead_status_id,
                'name' => $this->lead_status_name,
                'color' => $this->lead_status_color,
            ],
            'tenant_id' => $this->tenant_id,
            'last_contacted_at' => $this->last_contacted_at?->toIso8601String(),
            'next_followup_at' => $this->next_followup_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
