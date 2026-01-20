<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Carbon\Carbon;

/**
 * Call Data Transfer Object
 *
 * Immutable DTO for type-safe call data transfer between layers.
 */
readonly class CallDTO
{
    public function __construct(
        public string $direction,
        public string $from_number,
        public string $to_number,
        public Carbon $started_at,
        public ?Carbon $ended_at = null,
        public ?int $duration_seconds = null,
        public ?string $pbx_call_id = null,
        public ?string $recording_url = null,
        public ?int $disposition_id = null,
        public ?string $wrapup_notes = null,
        public ?string $related_type = null,
        public ?int $related_id = null,
        public ?int $user_id = null,
        public ?int $tenant_id = null,
    ) {
        $this->validateDirection($direction);
        $this->validatePhoneNumbers($from_number, $to_number);
        $this->validateDuration($duration_seconds);
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
        $validator = Validator::make($data, static::validate());
        
        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid call data: ' . $validator->errors()->first()
            );
        }

        return new self(
            direction: $data['direction'],
            from_number: $data['from_number'],
            to_number: $data['to_number'],
            started_at: $data['started_at'] instanceof Carbon ? $data['started_at'] : Carbon::parse($data['started_at']),
            ended_at: isset($data['ended_at']) ? ($data['ended_at'] instanceof Carbon ? $data['ended_at'] : Carbon::parse($data['ended_at'])) : null,
            duration_seconds: isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null,
            pbx_call_id: $data['pbx_call_id'] ?? null,
            recording_url: $data['recording_url'] ?? null,
            disposition_id: isset($data['disposition_id']) ? (int) $data['disposition_id'] : null,
            wrapup_notes: $data['wrapup_notes'] ?? null,
            related_type: $data['related_type'] ?? null,
            related_id: isset($data['related_id']) ? (int) $data['related_id'] : null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
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
            'direction' => $request->input('direction'),
            'from_number' => $request->input('from_number'),
            'to_number' => $request->input('to_number'),
            'started_at' => $request->input('started_at', now()),
            'ended_at' => $request->input('ended_at'),
            'duration_seconds' => $request->input('duration_seconds'),
            'pbx_call_id' => $request->input('pbx_call_id'),
            'recording_url' => $request->input('recording_url'),
            'disposition_id' => $request->input('disposition_id'),
            'wrapup_notes' => $request->input('wrapup_notes'),
            'related_type' => $request->input('related_type'),
            'related_id' => $request->input('related_id'),
            'user_id' => $request->user()?->id,
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
            'direction' => $this->direction,
            'from_number' => $this->from_number,
            'to_number' => $this->to_number,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'duration_seconds' => $this->duration_seconds,
            'pbx_call_id' => $this->pbx_call_id,
            'recording_url' => $this->recording_url,
            'disposition_id' => $this->disposition_id,
            'wrapup_notes' => $this->wrapup_notes,
            'related_type' => $this->related_type,
            'related_id' => $this->related_id,
            'user_id' => $this->user_id,
            'tenant_id' => $this->tenant_id,
        ], fn($value) => $value !== null);
    }

    /**
     * Get validation rules for call data
     *
     * @return array<string, mixed>
     */
    public static function validate(): array
    {
        return [
            'direction' => ['required', 'in:inbound,outbound'],
            'from_number' => ['required', 'string', 'max:20'],
            'to_number' => ['required', 'string', 'max:20'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'pbx_call_id' => ['nullable', 'string', 'max:255'],
            'recording_url' => ['nullable', 'url', 'max:500'],
            'disposition_id' => ['nullable', 'integer', 'exists:call_dispositions,id'],
            'wrapup_notes' => ['nullable', 'string', 'max:5000'],
            'related_type' => ['nullable', 'string', 'in:lead'],
            'related_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
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
        return self::fromArray(array_merge($this->toArray(), $updates));
    }

    /**
     * Calculate duration if ended
     *
     * @return int|null Duration in seconds
     */
    public function calculateDuration(): ?int
    {
        if ($this->ended_at === null) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->ended_at);
    }

    /**
     * Check if call is completed
     */
    public function isCompleted(): bool
    {
        return $this->ended_at !== null;
    }

    /**
     * Validate direction value
     *
     * @throws InvalidArgumentException
     */
    private function validateDirection(string $direction): void
    {
        if (!in_array($direction, ['inbound', 'outbound'])) {
            throw new InvalidArgumentException("Invalid direction: {$direction}. Must be either 'inbound' or 'outbound'");
        }
    }

    /**
     * Validate phone numbers
     *
     * @throws InvalidArgumentException
     */
    private function validatePhoneNumbers(string $from, string $to): void
    {
        if (empty($from) || empty($to)) {
            throw new InvalidArgumentException("Phone numbers cannot be empty");
        }

        if (strlen($from) > 20 || strlen($to) > 20) {
            throw new InvalidArgumentException("Phone numbers cannot exceed 20 characters");
        }
    }

    /**
     * Validate duration
     *
     * @throws InvalidArgumentException
     */
    private function validateDuration(?int $duration): void
    {
        if ($duration !== null && ($duration < 0 || $duration > 86400)) {
            throw new InvalidArgumentException("Duration must be between 0 and 86400 seconds (24 hours)");
        }
    }
}
