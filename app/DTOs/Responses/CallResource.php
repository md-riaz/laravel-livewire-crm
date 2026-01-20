<?php

namespace App\DTOs\Responses;

use App\Models\Call;
use Carbon\Carbon;

/**
 * Call Resource DTO
 *
 * Response DTO for single call representation.
 * Provides consistent API response structure.
 */
readonly class CallResource
{
    public function __construct(
        public int $id,
        public string $direction,
        public string $from_number,
        public string $to_number,
        public Carbon $started_at,
        public ?Carbon $ended_at,
        public ?int $duration_seconds,
        public ?string $pbx_call_id,
        public ?string $recording_url,
        public ?int $disposition_id,
        public ?string $disposition_name,
        public ?string $wrapup_notes,
        public ?string $related_type,
        public ?int $related_id,
        public int $user_id,
        public string $user_name,
        public int $tenant_id,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Create resource from Call model
     *
     * @param Call $call
     * @return self
     */
    public static function fromModel(Call $call): self
    {
        return new self(
            id: $call->id,
            direction: $call->direction,
            from_number: $call->from_number,
            to_number: $call->to_number,
            started_at: $call->started_at,
            ended_at: $call->ended_at,
            duration_seconds: $call->duration_seconds,
            pbx_call_id: $call->pbx_call_id,
            recording_url: $call->recording_url,
            disposition_id: $call->disposition_id,
            disposition_name: $call->disposition?->name,
            wrapup_notes: $call->wrapup_notes,
            related_type: $call->related_type,
            related_id: $call->related_id,
            user_id: $call->user_id,
            user_name: $call->user?->name ?? 'Unknown',
            tenant_id: $call->tenant_id,
            created_at: $call->created_at,
            updated_at: $call->updated_at,
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
            'direction' => $this->direction,
            'from_number' => $this->from_number,
            'to_number' => $this->to_number,
            'started_at' => $this->started_at->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'duration_seconds' => $this->duration_seconds,
            'duration_formatted' => $this->formatDuration(),
            'pbx_call_id' => $this->pbx_call_id,
            'recording_url' => $this->recording_url,
            'disposition' => [
                'id' => $this->disposition_id,
                'name' => $this->disposition_name,
            ],
            'wrapup_notes' => $this->wrapup_notes,
            'related' => [
                'type' => $this->related_type,
                'id' => $this->related_id,
            ],
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user_name,
            ],
            'tenant_id' => $this->tenant_id,
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

    /**
     * Format duration in human-readable format
     */
    private function formatDuration(): ?string
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Check if call is completed
     */
    public function isCompleted(): bool
    {
        return $this->ended_at !== null;
    }
}
