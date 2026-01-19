<?php

namespace App\Builders;

use App\DTOs\CallDTO;
use Carbon\Carbon;

/**
 * Call DTO Builder
 *
 * Fluent builder for constructing CallDTO instances.
 * Provides a chainable interface for setting call properties.
 */
class CallDTOBuilder
{
    private string $direction;
    private string $from_number;
    private string $to_number;
    private Carbon $started_at;
    private ?Carbon $ended_at = null;
    private ?int $duration_seconds = null;
    private ?string $pbx_call_id = null;
    private ?string $recording_url = null;
    private ?int $disposition_id = null;
    private ?string $wrapup_notes = null;
    private ?string $related_type = null;
    private ?int $related_id = null;
    private ?int $user_id = null;
    private ?int $tenant_id = null;

    /**
     * Set call as inbound
     */
    public function inbound(string $from_number, string $to_number): self
    {
        $this->direction = 'inbound';
        $this->from_number = $from_number;
        $this->to_number = $to_number;
        return $this;
    }

    /**
     * Set call as outbound
     */
    public function outbound(string $from_number, string $to_number): self
    {
        $this->direction = 'outbound';
        $this->from_number = $from_number;
        $this->to_number = $to_number;
        return $this;
    }

    /**
     * Set call direction
     */
    public function withDirection(string $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * Set from number
     */
    public function fromNumber(string $from_number): self
    {
        $this->from_number = $from_number;
        return $this;
    }

    /**
     * Set to number
     */
    public function toNumber(string $to_number): self
    {
        $this->to_number = $to_number;
        return $this;
    }

    /**
     * Set started at timestamp
     */
    public function startedAt(Carbon|string $started_at): self
    {
        $this->started_at = $started_at instanceof Carbon ? $started_at : Carbon::parse($started_at);
        return $this;
    }

    /**
     * Set started at to now
     */
    public function startedNow(): self
    {
        $this->started_at = Carbon::now();
        return $this;
    }

    /**
     * Set ended at timestamp
     */
    public function endedAt(?Carbon $ended_at): self
    {
        $this->ended_at = $ended_at;
        return $this;
    }

    /**
     * Set ended at to now
     */
    public function endedNow(): self
    {
        $this->ended_at = Carbon::now();
        return $this;
    }

    /**
     * Set duration in seconds
     */
    public function withDuration(?int $duration_seconds): self
    {
        $this->duration_seconds = $duration_seconds;
        return $this;
    }

    /**
     * Set PBX call ID
     */
    public function withPbxCallId(?string $pbx_call_id): self
    {
        $this->pbx_call_id = $pbx_call_id;
        return $this;
    }

    /**
     * Set recording URL
     */
    public function withRecording(?string $recording_url): self
    {
        $this->recording_url = $recording_url;
        return $this;
    }

    /**
     * Set disposition ID
     */
    public function withDisposition(?int $disposition_id): self
    {
        $this->disposition_id = $disposition_id;
        return $this;
    }

    /**
     * Set wrap-up notes
     */
    public function withWrapUpNotes(?string $wrapup_notes): self
    {
        $this->wrapup_notes = $wrapup_notes;
        return $this;
    }

    /**
     * Set related entity
     */
    public function relatedTo(?string $type, ?int $id): self
    {
        $this->related_type = $type;
        $this->related_id = $id;
        return $this;
    }

    /**
     * Set related to lead
     */
    public function relatedToLead(?int $lead_id): self
    {
        $this->related_type = 'lead';
        $this->related_id = $lead_id;
        return $this;
    }

    /**
     * Set user ID
     */
    public function forUser(?int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * Set tenant ID
     */
    public function forTenant(?int $tenant_id): self
    {
        $this->tenant_id = $tenant_id;
        return $this;
    }

    /**
     * Build the CallDTO instance
     *
     * @return CallDTO
     */
    public function build(): CallDTO
    {
        if (!isset($this->direction)) {
            throw new \LogicException('Call direction is required. Call inbound() or outbound() before build().');
        }

        if (!isset($this->from_number) || !isset($this->to_number)) {
            throw new \LogicException('Phone numbers are required. Set from() and to() before build().');
        }

        if (!isset($this->started_at)) {
            $this->started_at = Carbon::now();
        }

        return new CallDTO(
            direction: $this->direction,
            from_number: $this->from_number,
            to_number: $this->to_number,
            started_at: $this->started_at,
            ended_at: $this->ended_at,
            duration_seconds: $this->duration_seconds,
            pbx_call_id: $this->pbx_call_id,
            recording_url: $this->recording_url,
            disposition_id: $this->disposition_id,
            wrapup_notes: $this->wrapup_notes,
            related_type: $this->related_type,
            related_id: $this->related_id,
            user_id: $this->user_id,
            tenant_id: $this->tenant_id,
        );
    }

    /**
     * Create a new builder instance
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Create builder from existing DTO
     */
    public static function fromDTO(CallDTO $dto): self
    {
        return (new self())
            ->withDirection($dto->direction)
            ->fromNumber($dto->from_number)
            ->toNumber($dto->to_number)
            ->startedAt($dto->started_at)
            ->endedAt($dto->ended_at)
            ->withDuration($dto->duration_seconds)
            ->withPbxCallId($dto->pbx_call_id)
            ->withRecording($dto->recording_url)
            ->withDisposition($dto->disposition_id)
            ->withWrapUpNotes($dto->wrapup_notes)
            ->relatedTo($dto->related_type, $dto->related_id)
            ->forUser($dto->user_id)
            ->forTenant($dto->tenant_id);
    }
}
