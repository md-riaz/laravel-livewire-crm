<?php

namespace Tests\Unit\Builders;

use App\Builders\LeadDTOBuilder;
use App\DTOs\LeadDTO;
use LogicException;
use Tests\TestCase;

/**
 * Lead DTO Builder Test
 *
 * Tests for LeadDTOBuilder fluent interface.
 */
class LeadDTOBuilderTest extends TestCase
{
    public function test_builds_dto_with_fluent_interface(): void
    {
        $dto = LeadDTOBuilder::make()
            ->withName('John Doe')
            ->withEmail('john@example.com')
            ->withPhone('+1234567890')
            ->asHot()
            ->withEstimatedValue(50000)
            ->build();
        
        $this->assertInstanceOf(LeadDTO::class, $dto);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('+1234567890', $dto->phone);
        $this->assertEquals('hot', $dto->score);
        $this->assertEquals(50000, $dto->estimated_value);
    }

    public function test_score_helper_methods(): void
    {
        $hot = LeadDTOBuilder::make()
            ->withName('Hot Lead')
            ->asHot()
            ->build();
        
        $warm = LeadDTOBuilder::make()
            ->withName('Warm Lead')
            ->asWarm()
            ->build();
        
        $cold = LeadDTOBuilder::make()
            ->withName('Cold Lead')
            ->asCold()
            ->build();
        
        $this->assertEquals('hot', $hot->score);
        $this->assertEquals('warm', $warm->score);
        $this->assertEquals('cold', $cold->score);
    }

    public function test_with_details_method(): void
    {
        $dto = LeadDTOBuilder::make()
            ->withDetails(
                name: 'John Doe',
                company_name: 'Acme Corp',
                email: 'john@acme.com',
                phone: '+1234567890'
            )
            ->build();
        
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('Acme Corp', $dto->company_name);
        $this->assertEquals('john@acme.com', $dto->email);
        $this->assertEquals('+1234567890', $dto->phone);
    }

    public function test_throws_exception_when_name_not_set(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Lead name is required');
        
        LeadDTOBuilder::make()
            ->withEmail('test@example.com')
            ->build();
    }

    public function test_creates_builder_from_existing_dto(): void
    {
        $original = new LeadDTO(
            name: 'John Doe',
            email: 'john@example.com',
            score: 'warm'
        );
        
        $updated = LeadDTOBuilder::from($original)
            ->asHot()
            ->withEstimatedValue(75000)
            ->build();
        
        $this->assertEquals('John Doe', $updated->name);
        $this->assertEquals('john@example.com', $updated->email);
        $this->assertEquals('hot', $updated->score);
        $this->assertEquals(75000, $updated->estimated_value);
    }
}
