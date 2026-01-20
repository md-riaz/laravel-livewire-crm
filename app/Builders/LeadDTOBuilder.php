<?php

namespace App\Builders;

use App\DTOs\LeadDTO;

/**
 * Lead DTO Builder
 *
 * Fluent builder for constructing LeadDTO instances.
 * Provides a chainable interface for setting lead properties.
 */
class LeadDTOBuilder
{
    private string $name;
    private ?string $company_name = null;
    private ?string $email = null;
    private ?string $phone = null;
    private ?string $source = null;
    private string $score = 'warm';
    private ?float $estimated_value = null;
    private ?int $assigned_to_user_id = null;
    private ?int $lead_status_id = null;
    private ?int $created_by_user_id = null;
    private ?int $tenant_id = null;

    /**
     * Set lead name
     */
    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set company name
     */
    public function withCompanyName(?string $company_name): self
    {
        $this->company_name = $company_name;
        return $this;
    }

    /**
     * Set email
     */
    public function withEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set phone
     */
    public function withPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Set source
     */
    public function withSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Set score
     */
    public function withScore(string $score): self
    {
        $this->score = $score;
        return $this;
    }

    /**
     * Set as hot lead
     */
    public function asHot(): self
    {
        $this->score = 'hot';
        return $this;
    }

    /**
     * Set as warm lead
     */
    public function asWarm(): self
    {
        $this->score = 'warm';
        return $this;
    }

    /**
     * Set as cold lead
     */
    public function asCold(): self
    {
        $this->score = 'cold';
        return $this;
    }

    /**
     * Set estimated value
     */
    public function withEstimatedValue(?float $estimated_value): self
    {
        $this->estimated_value = $estimated_value;
        return $this;
    }

    /**
     * Set assigned user ID
     */
    public function assignedTo(?int $user_id): self
    {
        $this->assigned_to_user_id = $user_id;
        return $this;
    }

    /**
     * Set lead status ID
     */
    public function withStatus(?int $status_id): self
    {
        $this->lead_status_id = $status_id;
        return $this;
    }

    /**
     * Set created by user ID
     */
    public function createdBy(?int $user_id): self
    {
        $this->created_by_user_id = $user_id;
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
     * Set contact information
     */
    public function withContact(?string $email, ?string $phone): self
    {
        $this->email = $email;
        $this->phone = $phone;
        return $this;
    }

    /**
     * Set full lead information
     */
    public function withDetails(
        string $name,
        ?string $company_name = null,
        ?string $email = null,
        ?string $phone = null
    ): self {
        $this->name = $name;
        $this->company_name = $company_name;
        $this->email = $email;
        $this->phone = $phone;
        return $this;
    }

    /**
     * Build the LeadDTO instance
     *
     * @return LeadDTO
     */
    public function build(): LeadDTO
    {
        if (!isset($this->name)) {
            throw new \LogicException('Lead name is required. Call withName() before build().');
        }

        return new LeadDTO(
            name: $this->name,
            company_name: $this->company_name,
            email: $this->email,
            phone: $this->phone,
            source: $this->source,
            score: $this->score,
            estimated_value: $this->estimated_value,
            assigned_to_user_id: $this->assigned_to_user_id,
            lead_status_id: $this->lead_status_id,
            created_by_user_id: $this->created_by_user_id,
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
    public static function from(LeadDTO $dto): self
    {
        return (new self())
            ->withName($dto->name)
            ->withCompanyName($dto->company_name)
            ->withEmail($dto->email)
            ->withPhone($dto->phone)
            ->withSource($dto->source)
            ->withScore($dto->score)
            ->withEstimatedValue($dto->estimated_value)
            ->assignedTo($dto->assigned_to_user_id)
            ->withStatus($dto->lead_status_id)
            ->createdBy($dto->created_by_user_id)
            ->forTenant($dto->tenant_id);
    }
}
