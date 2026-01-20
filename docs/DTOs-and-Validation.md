# Data Transfer Objects (DTOs) and Validation Layer

This document describes the enterprise-level DTO and validation layer implementation for the Laravel Livewire CRM.

## Overview

The DTO layer provides type-safe, immutable data transfer objects with comprehensive validation for enterprise applications. This implementation follows best practices for multi-tenant SaaS applications.

## Architecture

```
app/
├── DTOs/
│   ├── LeadDTO.php              # Lead data transfer object
│   ├── CallDTO.php              # Call data transfer object
│   ├── CallWrapUpDTO.php        # Call wrap-up data
│   ├── LeadSearchDTO.php        # Lead search filters
│   └── Responses/
│       ├── LeadResource.php     # Single lead response
│       ├── LeadCollection.php   # Multiple leads response
│       ├── CallResource.php     # Single call response
│       └── CallCollection.php   # Multiple calls response
├── Rules/
│   ├── TenantExists.php         # Validates tenant ownership
│   ├── LeadStatusExists.php     # Validates status in tenant
│   ├── PhoneNumberFormat.php    # Validates phone format
│   └── UniqueEmailInTenant.php  # Email uniqueness per tenant
├── Http/Requests/
│   ├── CreateLeadRequest.php    # Lead creation validation
│   ├── UpdateLeadRequest.php    # Lead update validation
│   ├── WrapUpCallRequest.php    # Call wrap-up validation
│   └── SearchLeadsRequest.php   # Lead search validation
├── Actions/
│   ├── CreateLeadAction.php     # Single-purpose lead creation
│   ├── UpdateLeadAction.php     # Single-purpose lead update
│   ├── MoveLeadAction.php       # Single-purpose status change
│   ├── AssignLeadAction.php     # Single-purpose assignment
│   └── WrapUpCallAction.php     # Single-purpose wrap-up
├── Builders/
│   ├── LeadDTOBuilder.php       # Fluent lead DTO builder
│   └── CallDTOBuilder.php       # Fluent call DTO builder
└── Services/
    └── ValidationService.php     # Centralized validation logic
```

## Features

### 1. Type-Safe DTOs
- **PHP 8.3+ readonly classes** for immutability
- **Strict type hints** for all properties
- **Named constructors** (fromArray, fromRequest)
- **Validation at construction** time

### 2. Multi-Tenant Validation
- **Tenant-aware validation rules**
- **Cross-entity validation**
- **Automatic tenant scoping**

### 3. Builder Pattern
- **Fluent interface** for DTO construction
- **Chainable methods** for readability
- **Type-safe building**

### 4. Action Classes
- **Single Responsibility Principle**
- **Dependency injection**
- **Testable business logic**

## Usage Examples

### Creating a Lead with DTO

```php
use App\DTOs\LeadDTO;
use App\Actions\CreateLeadAction;

// Method 1: From array
$dto = LeadDTO::fromArray([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'score' => 'hot',
    'estimated_value' => 50000,
    'tenant_id' => auth()->user()->tenant_id,
]);

// Method 2: From request
$dto = LeadDTO::fromRequest($request);

// Method 3: Using builder
$dto = LeadDTOBuilder::make()
    ->withName('John Doe')
    ->withEmail('john@example.com')
    ->withPhone('+1234567890')
    ->asHot()
    ->withEstimatedValue(50000)
    ->forTenant(auth()->user()->tenant_id)
    ->build();

// Execute action
$lead = app(CreateLeadAction::class)->execute($dto);
```

### Using Form Requests

```php
use App\Http\Requests\CreateLeadRequest;
use App\Actions\CreateLeadAction;

public function store(CreateLeadRequest $request, CreateLeadAction $action)
{
    // Request is automatically validated
    $dto = $request->toDTO();
    
    $lead = $action->execute($dto);
    
    return response()->json([
        'message' => 'Lead created successfully',
        'data' => LeadResource::fromModel($lead)->toArray(),
    ], 201);
}
```

### Searching Leads

```php
use App\Http\Requests\SearchLeadsRequest;
use App\DTOs\Responses\LeadCollection;
use App\Models\Lead;

public function search(SearchLeadsRequest $request)
{
    $searchDTO = $request->toDTO();
    
    $query = Lead::query()
        ->where('tenant_id', $searchDTO->tenant_id);
    
    if ($searchDTO->search) {
        $query->where('name', 'like', "%{$searchDTO->search}%");
    }
    
    if ($searchDTO->score) {
        $query->where('score', $searchDTO->score);
    }
    
    $leads = $query
        ->orderBy($searchDTO->sort_by, $searchDTO->sort_direction)
        ->paginate($searchDTO->per_page);
    
    return response()->json(
        LeadCollection::fromPaginator($leads)->toArray()
    );
}
```

### Wrapping Up a Call

```php
use App\Http\Requests\WrapUpCallRequest;
use App\Actions\WrapUpCallAction;

public function wrapUp(Call $call, WrapUpCallRequest $request, WrapUpCallAction $action)
{
    $dto = $request->toDTO();
    
    $updatedCall = $action->execute($dto);
    
    return response()->json([
        'message' => 'Call wrapped up successfully',
        'data' => CallResource::fromModel($updatedCall)->toArray(),
    ]);
}
```

### Using Custom Validation Rules

```php
use App\Rules\PhoneNumberFormat;
use App\Rules\UniqueEmailInTenant;
use App\Rules\LeadStatusExists;

// In a form request
public function rules(): array
{
    return [
        'phone' => ['required', new PhoneNumberFormat()],
        'email' => ['required', 'email', new UniqueEmailInTenant()],
        'lead_status_id' => ['required', new LeadStatusExists()],
    ];
}
```

### Using Validation Service

```php
use App\Services\ValidationService;

public function assignLead(Lead $lead, User $user)
{
    $validator = app(ValidationService::class);
    
    // Throws ValidationException if invalid
    $validator->validateLeadAssignment(
        $lead->id,
        $user->id,
        auth()->user()->tenant_id
    );
    
    // Proceed with assignment
    $lead->update(['assigned_to_user_id' => $user->id]);
}
```

### Response DTOs

```php
use App\DTOs\Responses\LeadResource;
use App\DTOs\Responses\LeadCollection;

// Single resource
$resource = LeadResource::fromModel($lead);
return response()->json($resource->toArray());

// Collection
$collection = LeadCollection::fromModels($leads);
return response()->json($collection->toArray());

// Paginated collection
$collection = LeadCollection::fromPaginator($paginatedLeads);
return response()->json($collection->toArray());
```

## Validation Rules

### TenantExists
Validates that a tenant_id belongs to the authenticated tenant.

```php
'tenant_id' => ['required', 'integer', new TenantExists()],
```

### LeadStatusExists
Validates that a lead status exists for the current tenant.

```php
'lead_status_id' => ['required', new LeadStatusExists()],
```

### PhoneNumberFormat
Validates phone number format (international support).

```php
'phone' => ['required', new PhoneNumberFormat()],
```

### UniqueEmailInTenant
Ensures email uniqueness within the tenant.

```php
'email' => ['required', 'email', new UniqueEmailInTenant()],
```

## Builder Pattern Usage

### Lead DTO Builder

```php
use App\Builders\LeadDTOBuilder;

$dto = LeadDTOBuilder::make()
    ->withDetails('John Doe', 'Acme Corp', 'john@acme.com', '+1234567890')
    ->withSource('website')
    ->asHot()
    ->withEstimatedValue(100000)
    ->assignedTo($userId)
    ->withStatus($statusId)
    ->forTenant($tenantId)
    ->build();
```

### Call DTO Builder

```php
use App\Builders\CallDTOBuilder;

$dto = CallDTOBuilder::make()
    ->outbound('+1234567890', '+0987654321')
    ->startedNow()
    ->relatedToLead($leadId)
    ->forUser($userId)
    ->forTenant($tenantId)
    ->build();

// Later, when call ends
$updatedDto = CallDTOBuilder::fromDTO($dto)
    ->endedNow()
    ->withDuration(180)
    ->withDisposition($dispositionId)
    ->build();
```

## Best Practices

### 1. Always Use DTOs for Data Transfer
```php
// ❌ Bad - passing arrays
$leadService->createLead($request->all());

// ✅ Good - using DTOs
$dto = LeadDTO::fromRequest($request);
$leadService->createLead($dto->toArray());
```

### 2. Use Actions for Business Logic
```php
// ❌ Bad - business logic in controller
public function store(Request $request)
{
    $lead = Lead::create($request->validated());
    event(new LeadCreated($lead));
    // more logic...
}

// ✅ Good - using actions
public function store(CreateLeadRequest $request, CreateLeadAction $action)
{
    $lead = $action->execute($request->toDTO());
    return response()->json(['data' => $lead]);
}
```

### 3. Use Response DTOs for API Responses
```php
// ❌ Bad - direct model serialization
return response()->json($lead);

// ✅ Good - using response DTOs
return response()->json(LeadResource::fromModel($lead)->toArray());
```

### 4. Validate at Multiple Layers
```php
// Layer 1: Form Request validation
// Layer 2: DTO construction validation
// Layer 3: Business rule validation in actions
// Layer 4: Database constraints
```

### 5. Use Builders for Complex Construction
```php
// ❌ Bad - complex array construction
$dto = LeadDTO::fromArray([
    'name' => $name,
    'email' => $email,
    // many fields...
]);

// ✅ Good - using builder
$dto = LeadDTOBuilder::make()
    ->withDetails($name, $company, $email, $phone)
    ->withScore($score)
    // chainable and readable
    ->build();
```

## Testing

### Testing DTOs

```php
use App\DTOs\LeadDTO;
use Tests\TestCase;

class LeadDTOTest extends TestCase
{
    public function test_creates_dto_from_array()
    {
        $dto = LeadDTO::fromArray([
            'name' => 'John Doe',
            'score' => 'hot',
        ]);
        
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('hot', $dto->score);
    }
    
    public function test_validates_score()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new LeadDTO(name: 'Test', score: 'invalid');
    }
}
```

### Testing Actions

```php
use App\Actions\CreateLeadAction;
use App\DTOs\LeadDTO;
use Tests\TestCase;

class CreateLeadActionTest extends TestCase
{
    public function test_creates_lead()
    {
        $dto = LeadDTO::fromArray([
            'name' => 'John Doe',
            'score' => 'hot',
            'tenant_id' => 1,
        ]);
        
        $action = app(CreateLeadAction::class);
        $lead = $action->execute($dto);
        
        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'tenant_id' => 1,
        ]);
    }
}
```

## Performance Considerations

1. **DTOs are lightweight** - readonly classes have minimal overhead
2. **Validation caching** - validation rules are cached by Laravel
3. **Response DTOs** - can be cached for expensive API responses
4. **Bulk operations** - use ValidationService for bulk validation

## Security

1. **Multi-tenant isolation** - all validation rules are tenant-aware
2. **Input sanitization** - handled by Laravel's validator
3. **Type safety** - prevents type juggling vulnerabilities
4. **Immutability** - prevents unexpected mutations

## Migration Guide

### From Array-based to DTO-based

```php
// Before
public function createLead(array $data): Lead
{
    return Lead::create($data);
}

// After
public function createLead(LeadDTO $dto): Lead
{
    return Lead::create($dto->toArray());
}
```

### From Controller Logic to Actions

```php
// Before
public function store(Request $request)
{
    $validated = $request->validate([...]);
    $lead = Lead::create($validated);
    event(new LeadCreated($lead));
    return response()->json($lead);
}

// After
public function store(CreateLeadRequest $request, CreateLeadAction $action)
{
    $lead = $action->execute($request->toDTO());
    return response()->json(LeadResource::fromModel($lead)->toArray());
}
```

## Extending the System

### Adding New DTOs

1. Create readonly class in `app/DTOs/`
2. Add validation rules
3. Add named constructors
4. Add toArray() method
5. Create corresponding builder

### Adding New Validation Rules

1. Create class in `app/Rules/`
2. Implement `ValidationRule` interface
3. Add tenant-awareness if needed
4. Add tests

### Adding New Actions

1. Create readonly class in `app/Actions/`
2. Inject dependencies via constructor
3. Implement single execute() method
4. Add tests

## Support

For questions or issues with the DTO layer:
1. Check this documentation
2. Review test files for examples
3. Check inline PHPDoc comments

## License

Part of the Laravel Livewire CRM project.
