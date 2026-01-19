<?php

namespace Tests\Unit\DTOs;

use App\DTOs\LeadDTO;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Lead DTO Test
 *
 * Tests for LeadDTO validation and construction.
 */
class LeadDTOTest extends TestCase
{
    public function test_creates_dto_from_array(): void
    {
        $dto = LeadDTO::fromArray([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'score' => 'hot',
        ]);
        
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('+1234567890', $dto->phone);
        $this->assertEquals('hot', $dto->score);
    }

    public function test_validates_required_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        LeadDTO::fromArray([
            'score' => 'warm',
        ]);
    }

    public function test_validates_score_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid score');
        
        new LeadDTO(
            name: 'Test Lead',
            score: 'invalid-score'
        );
    }

    public function test_validates_estimated_value_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Estimated value must be between');
        
        new LeadDTO(
            name: 'Test Lead',
            estimated_value: -100
        );
    }

    public function test_converts_to_array(): void
    {
        $dto = new LeadDTO(
            name: 'John Doe',
            email: 'john@example.com',
            score: 'hot',
            estimated_value: 50000
        );
        
        $array = $dto->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('John Doe', $array['name']);
        $this->assertEquals('john@example.com', $array['email']);
        $this->assertEquals('hot', $array['score']);
        $this->assertEquals(50000, $array['estimated_value']);
    }

    public function test_with_method_creates_new_immutable_instance(): void
    {
        $original = new LeadDTO(
            name: 'John Doe',
            score: 'warm'
        );
        
        $updated = $original->with(['score' => 'hot']);
        
        $this->assertEquals('warm', $original->score);
        $this->assertEquals('hot', $updated->score);
        $this->assertNotSame($original, $updated);
    }

    public function test_filters_null_values_in_to_array(): void
    {
        $dto = new LeadDTO(
            name: 'John Doe',
            score: 'warm'
        );
        
        $array = $dto->toArray();
        
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('score', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('phone', $array);
    }
}
