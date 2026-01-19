<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Call Wrap-Up Data Transfer Object
 *
 * Immutable DTO for call wrap-up data after a call is completed.
 */
readonly class CallWrapUpDTO
{
    public function __construct(
        public int $call_id,
        public ?int $disposition_id = null,
        public ?string $wrapup_notes = null,
        public ?int $related_id = null,
        public ?string $related_type = null,
        public ?array $follow_up_actions = null,
    ) {
        $this->validateWrapUpNotes($wrapup_notes);
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
                'Invalid wrap-up data: ' . $validator->errors()->first()
            );
        }

        return new self(
            call_id: (int) $data['call_id'],
            disposition_id: isset($data['disposition_id']) ? (int) $data['disposition_id'] : null,
            wrapup_notes: $data['wrapup_notes'] ?? null,
            related_id: isset($data['related_id']) ? (int) $data['related_id'] : null,
            related_type: $data['related_type'] ?? null,
            follow_up_actions: $data['follow_up_actions'] ?? null,
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
            'call_id' => $request->input('call_id'),
            'disposition_id' => $request->input('disposition_id'),
            'wrapup_notes' => $request->input('wrapup_notes'),
            'related_id' => $request->input('related_id'),
            'related_type' => $request->input('related_type'),
            'follow_up_actions' => $request->input('follow_up_actions'),
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
            'call_id' => $this->call_id,
            'disposition_id' => $this->disposition_id,
            'wrapup_notes' => $this->wrapup_notes,
            'related_id' => $this->related_id,
            'related_type' => $this->related_type,
            'follow_up_actions' => $this->follow_up_actions,
        ], fn($value) => $value !== null);
    }

    /**
     * Get validation rules for wrap-up data
     *
     * @return array<string, mixed>
     */
    public static function validate(): array
    {
        return [
            'call_id' => ['required', 'integer', 'exists:calls,id'],
            'disposition_id' => ['nullable', 'integer', 'exists:call_dispositions,id'],
            'wrapup_notes' => ['nullable', 'string', 'max:5000'],
            'related_id' => ['nullable', 'integer'],
            'related_type' => ['nullable', 'string', 'in:lead'],
            'follow_up_actions' => ['nullable', 'array'],
            'follow_up_actions.*' => ['string'],
        ];
    }

    /**
     * Check if follow-up is required
     */
    public function requiresFollowUp(): bool
    {
        return !empty($this->follow_up_actions);
    }

    /**
     * Validate wrap-up notes
     *
     * @throws InvalidArgumentException
     */
    private function validateWrapUpNotes(?string $notes): void
    {
        if ($notes !== null && strlen($notes) > 5000) {
            throw new InvalidArgumentException("Wrap-up notes cannot exceed 5000 characters");
        }
    }
}
