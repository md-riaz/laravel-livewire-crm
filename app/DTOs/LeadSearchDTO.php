<?php

namespace App\DTOs;

use Illuminate\Http\Request;

/**
 * Lead Search Data Transfer Object
 *
 * Immutable DTO for lead search filters and pagination.
 */
readonly class LeadSearchDTO
{
    public function __construct(
        public ?string $search = null,
        public ?int $lead_status_id = null,
        public ?int $assigned_to_user_id = null,
        public ?string $score = null,
        public ?string $source = null,
        public ?float $min_estimated_value = null,
        public ?float $max_estimated_value = null,
        public ?string $sort_by = 'created_at',
        public ?string $sort_direction = 'desc',
        public ?int $per_page = 15,
        public ?int $page = 1,
        public ?int $tenant_id = null,
    ) {}

    /**
     * Create DTO from array data
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            lead_status_id: isset($data['lead_status_id']) ? (int) $data['lead_status_id'] : null,
            assigned_to_user_id: isset($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null,
            score: $data['score'] ?? null,
            source: $data['source'] ?? null,
            min_estimated_value: isset($data['min_estimated_value']) ? (float) $data['min_estimated_value'] : null,
            max_estimated_value: isset($data['max_estimated_value']) ? (float) $data['max_estimated_value'] : null,
            sort_by: $data['sort_by'] ?? 'created_at',
            sort_direction: $data['sort_direction'] ?? 'desc',
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 15,
            page: isset($data['page']) ? (int) $data['page'] : 1,
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
            'search' => $request->input('search'),
            'lead_status_id' => $request->input('lead_status_id'),
            'assigned_to_user_id' => $request->input('assigned_to_user_id'),
            'score' => $request->input('score'),
            'source' => $request->input('source'),
            'min_estimated_value' => $request->input('min_estimated_value'),
            'max_estimated_value' => $request->input('max_estimated_value'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_direction' => $request->input('sort_direction', 'desc'),
            'per_page' => $request->input('per_page', 15),
            'page' => $request->input('page', 1),
            'tenant_id' => $request->user()?->tenant_id,
        ]);
    }

    /**
     * Convert DTO to array for query building
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'lead_status_id' => $this->lead_status_id,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'score' => $this->score,
            'source' => $this->source,
            'min_estimated_value' => $this->min_estimated_value,
            'max_estimated_value' => $this->max_estimated_value,
            'sort_by' => $this->sort_by,
            'sort_direction' => $this->sort_direction,
            'per_page' => $this->per_page,
            'page' => $this->page,
            'tenant_id' => $this->tenant_id,
        ], fn($value) => $value !== null);
    }

    /**
     * Check if any filters are applied
     */
    public function hasFilters(): bool
    {
        return $this->search !== null
            || $this->lead_status_id !== null
            || $this->assigned_to_user_id !== null
            || $this->score !== null
            || $this->source !== null
            || $this->min_estimated_value !== null
            || $this->max_estimated_value !== null;
    }

    /**
     * Get active filter count
     */
    public function getActiveFilterCount(): int
    {
        $count = 0;
        
        if ($this->search !== null) $count++;
        if ($this->lead_status_id !== null) $count++;
        if ($this->assigned_to_user_id !== null) $count++;
        if ($this->score !== null) $count++;
        if ($this->source !== null) $count++;
        if ($this->min_estimated_value !== null) $count++;
        if ($this->max_estimated_value !== null) $count++;
        
        return $count;
    }
}
