# Enterprise Architecture Documentation

## Overview

This Laravel Livewire CRM has been enhanced with enterprise-level architecture patterns focusing on extensibility, maintainability, and dynamic behaviors. The implementation follows SOLID principles and leverages modern PHP 8.3+ features.

## Architecture Components

### 1. Contracts/Interfaces Layer (`app/Contracts/`)

Defines the contracts for key services, promoting dependency inversion and testability.

#### Available Interfaces:
- **LeadServiceInterface** - Lead management operations
- **CallServiceInterface** - Call lifecycle management
- **ActivityLoggerInterface** - Activity logging and audit trails
- **LeadRepositoryInterface** - Lead data access patterns
- **CallRepositoryInterface** - Call data access patterns

### 2. Service Layer (`app/Services/`)

Implements business logic with dependency injection and transaction management.

#### Services:
- **LeadService** - Implements `LeadServiceInterface`
- **CallService** - Implements `CallServiceInterface`
- **ActivityLoggerService** - Implements `ActivityLoggerInterface`

### 3. Repository Pattern (`app/Repositories/`)

Handles complex query logic and data access, abstracting the data layer.

#### Repositories:
- **LeadRepository** - Lead data operations
- **CallRepository** - Call data operations

### 4. Event/Listener Architecture (`app/Events/`, `app/Listeners/`)

Decouples side effects from main business logic using Laravel's event system.

#### Events:
- `LeadCreated` - Dispatched when a lead is created
- `LeadStatusChanged` - Dispatched when a lead status changes
- `CallStarted` - Dispatched when a call starts
- `CallEnded` - Dispatched when a call ends
- `CallWrappedUp` - Dispatched when a call is wrapped up

#### Listeners:
- `LogLeadActivity` - Logs lead-related activities
- `LogCallActivity` - Logs call-related activities
- `UpdateLeadTimestamps` - Updates lead timestamps on call completion

### 5. Pipeline Pattern (`app/Pipelines/LeadPipeline/`)

Processes leads through multiple stages for validation, enrichment, and assignments.

#### Pipeline Stages:
- **ValidateLeadData** - Validates incoming lead data
- **EnrichLeadData** - Enriches data with defaults and derived information
- **AssignDefaultStatus** - Assigns default lead status
- **NotifyAssignedUser** - Sends notifications on assignment

### 6. Configuration-Driven Behaviors (`config/crm.php`)

Centralized configuration for customizable CRM behaviors.

## Usage Examples

### Creating a Lead with Service Layer

```php
use App\Contracts\LeadServiceInterface;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadServiceInterface $leadService
    ) {}

    public function store(Request $request)
    {
        $lead = $this->leadService->createLead([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'source' => $request->source,
            'score' => $request->score,
            'estimated_value' => $request->estimated_value,
        ]);

        return response()->json($lead, 201);
    }
}
```

### Managing Call Lifecycle

```php
use App\Contracts\CallServiceInterface;

class CallController extends Controller
{
    public function __construct(
        private readonly CallServiceInterface $callService
    ) {}

    public function start(Request $request)
    {
        $call = $this->callService->startCall([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'direction' => 'outbound',
            'from_number' => $request->from_number,
            'to_number' => $request->to_number,
            'lead_id' => $request->lead_id, // Auto-linked if configured
        ]);

        return response()->json($call);
    }

    public function end(Call $call)
    {
        $call = $this->callService->endCall($call);
        return response()->json($call);
    }

    public function wrapUp(Call $call, Request $request)
    {
        $call = $this->callService->wrapUpCall(
            $call,
            $request->disposition_id,
            $request->notes
        );

        return response()->json($call);
    }
}
```

### Searching Leads with Repository

```php
use App\Contracts\LeadRepositoryInterface;

class LeadSearchController extends Controller
{
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository
    ) {}

    public function search(Request $request)
    {
        $leads = $this->leadRepository->search([
            'status_id' => $request->status_id,
            'assigned_to' => $request->assigned_to,
            'score' => $request->score,
            'search' => $request->query,
            'min_value' => $request->min_value,
            'max_value' => $request->max_value,
            'created_from' => $request->created_from,
            'created_to' => $request->created_to,
            'order_by' => $request->order_by ?? 'created_at',
            'order_direction' => $request->order_direction ?? 'desc',
        ]);

        return response()->json($leads);
    }
}
```

### Custom Event Listeners

```php
// In EventServiceProvider or AppServiceProvider
Event::listen(LeadCreated::class, function (LeadCreated $event) {
    // Send welcome email
    // Trigger external integrations
    // Update analytics
});
```

### Using Activity Logger

```php
use App\Contracts\ActivityLoggerInterface;

class CustomController extends Controller
{
    public function __construct(
        private readonly ActivityLoggerInterface $activityLogger
    ) {}

    public function customAction(Lead $lead)
    {
        // Your business logic here
        
        // Log the activity
        $this->activityLogger->logForLead($lead, 'custom_action', [
            'detail1' => 'value1',
            'detail2' => 'value2',
        ]);
    }
}
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Lead Configuration
CRM_DEFAULT_LEAD_SCORE=warm
CRM_REQUIRE_LEAD_ASSIGNMENT=false
CRM_AUTO_LOG_ACTIVITIES=true
CRM_AUTO_ADJUST_SCORE=false

# Call Configuration
CRM_MANDATORY_WRAPUP=true
CRM_AUTO_LINK_CALLS=true
CRM_RECORDING_RETENTION=90
CRM_ENABLE_RECORDING=true
CRM_MAX_CALL_DURATION=0

# Feature Flags
CRM_FEATURE_TELEPHONY=true
CRM_FEATURE_EMAIL=false
CRM_FEATURE_REPORTING=false
CRM_FEATURE_SMS=false
CRM_FEATURE_WORKFLOWS=false
CRM_FEATURE_API=true

# Pipeline Configuration
CRM_ENABLE_ENRICHMENT=false
CRM_STRICT_VALIDATION=true
CRM_NOTIFY_ASSIGNMENT=true

# Business Rules
CRM_MIN_QUALIFIED_VALUE=0
CRM_STALE_LEAD_DAYS=30
CRM_MAX_LEADS_PER_USER=0
CRM_HIGH_VALUE_THRESHOLD=10000
```

## Testing

### Unit Testing Services

```php
use Tests\TestCase;
use App\Contracts\LeadServiceInterface;
use App\Contracts\ActivityLoggerInterface;
use Mockery;

class LeadServiceTest extends TestCase
{
    public function test_creates_lead_with_pipeline()
    {
        $activityLogger = Mockery::mock(ActivityLoggerInterface::class);
        $activityLogger->shouldReceive('logForLead')->once();

        app()->instance(ActivityLoggerInterface::class, $activityLogger);

        $service = app(LeadServiceInterface::class);
        
        $lead = $service->createLead([
            'tenant_id' => 1,
            'name' => 'Test Lead',
            'email' => 'test@example.com',
        ]);

        $this->assertNotNull($lead->id);
        $this->assertEquals('Test Lead', $lead->name);
    }
}
```

## Extensibility

### Adding Custom Pipeline Stages

```php
namespace App\Pipelines\LeadPipeline;

use Closure;

class CustomValidation
{
    public function handle(array $data, Closure $next): mixed
    {
        // Your custom validation logic
        
        return $next($data);
    }
}
```

Then add to the pipeline in `LeadService::createLead()`:

```php
$processedData = app(Pipeline::class)
    ->send($data)
    ->through([
        ValidateLeadData::class,
        CustomValidation::class, // Your custom stage
        EnrichLeadData::class,
        AssignDefaultStatus::class,
    ])
    ->thenReturn();
```

### Creating Custom Events

```php
namespace App\Events;

use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;

class LeadQualified
{
    use Dispatchable;

    public function __construct(
        public readonly Lead $lead
    ) {}
}
```

## Benefits

1. **Testability** - Interfaces enable easy mocking and unit testing
2. **Maintainability** - Clear separation of concerns
3. **Extensibility** - Easy to add new features without modifying existing code
4. **Scalability** - Repository pattern supports complex queries and caching
5. **Configurability** - Behavior changes without code modifications
6. **Auditability** - Comprehensive activity logging
7. **Type Safety** - Full type hints and PHP 8.3+ features
8. **Async Processing** - Events can be queued for background processing

## Best Practices

1. Always depend on interfaces, not concrete implementations
2. Use repositories for data access, not direct Eloquent calls
3. Emit events for side effects, keep services focused
4. Configure behavior through `config/crm.php` rather than hardcoding
5. Use pipelines for multi-step data processing
6. Log significant activities for audit trails
7. Keep services focused on business logic, not data access
8. Use transactions for operations affecting multiple tables

## Migration Notes

When updating existing code:

1. Replace direct Eloquent calls with repository methods
2. Replace inline business logic with service layer calls
3. Add event dispatching for side effects
4. Configure behaviors in `config/crm.php`
5. Use dependency injection for services and repositories

## Performance Considerations

- Events marked as `ShouldQueue` are processed asynchronously
- Repository methods use eager loading to prevent N+1 queries
- Use readonly classes where state doesn't change
- Pipeline stages are lightweight and efficient
- Configuration is cached in production

## Support

For questions or issues related to the architecture:
1. Review this documentation
2. Check configuration in `config/crm.php`
3. Examine service implementations in `app/Services/`
4. Review event listeners in `app/Listeners/`
