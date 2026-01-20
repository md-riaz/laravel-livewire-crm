# Enterprise Architecture Quick Reference

## File Structure

```
app/
├── Contracts/                      # Interfaces defining service contracts
│   ├── ActivityLoggerInterface.php
│   ├── CallRepositoryInterface.php
│   ├── CallServiceInterface.php
│   ├── LeadRepositoryInterface.php
│   └── LeadServiceInterface.php
├── Services/                       # Business logic implementations
│   ├── ActivityLoggerService.php
│   ├── CallService.php
│   ├── LeadService.php
│   └── TenantService.php
├── Repositories/                   # Data access layer
│   ├── CallRepository.php
│   └── LeadRepository.php
├── Events/                         # Domain events
│   ├── CallEnded.php
│   ├── CallStarted.php
│   ├── CallWrappedUp.php
│   ├── LeadAssigned.php
│   ├── LeadCreated.php
│   └── LeadStatusChanged.php
├── Listeners/                      # Event handlers
│   ├── LogCallActivity.php
│   ├── LogLeadActivity.php
│   ├── NotifyAssignedUser.php
│   └── UpdateLeadTimestamps.php
├── Pipelines/LeadPipeline/        # Pipeline stages
│   ├── AssignDefaultStatus.php
│   ├── EnrichLeadData.php
│   ├── NotifyAssignedUser.php
│   └── ValidateLeadData.php
├── Http/Controllers/Api/          # Example implementations
│   ├── CallController.php
│   └── LeadController.php
└── Providers/
    └── AppServiceProvider.php     # DI bindings and event registration
```

## Key Design Patterns

### 1. Dependency Injection
All services depend on interfaces, not concrete implementations:

```php
public function __construct(
    private readonly LeadServiceInterface $leadService,
    private readonly LeadRepositoryInterface $leadRepository
) {}
```

### 2. Repository Pattern
Separate data access from business logic:

```php
// Use repository for queries
$leads = $this->leadRepository->search($filters);

// Use service for business operations
$lead = $this->leadService->createLead($data);
```

### 3. Service Layer
Encapsulate business logic with transaction support:

```php
return DB::transaction(function () use ($lead, $data) {
    // Business logic here
    // Events dispatched
    // Activity logged
    return $updated;
});
```

### 4. Event-Driven Architecture
Decouple side effects from main logic:

```php
// Service dispatches event
event(new LeadCreated($lead));

// Listener handles side effects
class LogLeadActivity implements ShouldQueue {
    public function handle(LeadCreated $event) {
        // Log activity asynchronously
    }
}
```

### 5. Pipeline Pattern
Process data through multiple stages:

```php
$processedData = app(Pipeline::class)
    ->send($data)
    ->through([
        ValidateLeadData::class,
        EnrichLeadData::class,
        AssignDefaultStatus::class,
    ])
    ->thenReturn();
```

## Service Bindings

All interfaces are bound in `AppServiceProvider`:

```php
public array $bindings = [
    LeadServiceInterface::class => LeadService::class,
    CallServiceInterface::class => CallService::class,
    LeadRepositoryInterface::class => LeadRepository::class,
    CallRepositoryInterface::class => CallRepository::class,
];

public array $singletons = [
    ActivityLoggerInterface::class => ActivityLoggerService::class,
];
```

## Configuration

All behavior is configurable via `config/crm.php`:

```php
// Example configurations
'leads' => [
    'default_score' => env('CRM_DEFAULT_LEAD_SCORE', 'warm'),
    'auto_create_activities' => env('CRM_AUTO_LOG_ACTIVITIES', true),
],

'calls' => [
    'mandatory_wrapup' => env('CRM_MANDATORY_WRAPUP', true),
    'auto_link_to_lead' => env('CRM_AUTO_LINK_CALLS', true),
],

'features' => [
    'telephony' => env('CRM_FEATURE_TELEPHONY', true),
    'email_integration' => env('CRM_FEATURE_EMAIL', false),
],
```

## Common Operations

### Creating a Lead
```php
$lead = app(LeadServiceInterface::class)->createLead([
    'tenant_id' => auth()->user()->tenant_id,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
]);
```

### Searching Leads
```php
$leads = app(LeadRepositoryInterface::class)->search([
    'status_id' => 1,
    'score' => 'hot',
    'search' => 'john',
]);
```

### Managing Calls
```php
$callService = app(CallServiceInterface::class);

// Start
$call = $callService->startCall([...]);

// End
$call = $callService->endCall($call);

// Wrap up
$call = $callService->wrapUpCall($call, $dispositionId, $notes);
```

### Logging Activities
```php
$logger = app(ActivityLoggerInterface::class);

// Generic log
$logger->log('action_name', $model, $metadata);

// Lead-specific log
$logger->logForLead($lead, 'custom_action', $payload);
```

## Events Flow

```
Action               → Event Dispatched       → Listeners Triggered
─────────────────────────────────────────────────────────────────────
Lead Created         → LeadCreated            → LogLeadActivity
Lead Status Changed  → LeadStatusChanged      → LogLeadActivity
Lead Assigned        → LeadAssigned           → NotifyAssignedUser
Call Started         → CallStarted            → LogCallActivity
Call Ended           → CallEnded              → LogCallActivity
Call Wrapped Up      → CallWrappedUp          → UpdateLeadTimestamps
```

## Testing Tips

### Mocking Services
```php
$mock = Mockery::mock(LeadServiceInterface::class);
$mock->shouldReceive('createLead')->once()->andReturn($lead);
app()->instance(LeadServiceInterface::class, $mock);
```

### Faking Events
```php
Event::fake([LeadCreated::class]);
// Test code
Event::assertDispatched(LeadCreated::class);
```

### Testing Pipelines
```php
$result = app(Pipeline::class)
    ->send($data)
    ->through([ValidateLeadData::class])
    ->thenReturn();
```

## Best Practices

1. ✅ Always inject interfaces, never concrete classes
2. ✅ Use repositories for data access
3. ✅ Use services for business logic
4. ✅ Dispatch events for side effects
5. ✅ Keep listeners small and focused
6. ✅ Use configuration for behavior changes
7. ✅ Log significant activities
8. ✅ Use transactions for multi-step operations
9. ✅ Use pipelines for multi-stage data processing
10. ✅ Keep controllers thin - delegate to services

## Environment Variables

Add these to your `.env` file:

```env
# Lead Management
CRM_DEFAULT_LEAD_SCORE=warm
CRM_REQUIRE_LEAD_ASSIGNMENT=false
CRM_AUTO_LOG_ACTIVITIES=true

# Call Management
CRM_MANDATORY_WRAPUP=true
CRM_AUTO_LINK_CALLS=true
CRM_RECORDING_RETENTION=90

# Features
CRM_FEATURE_TELEPHONY=true
CRM_FEATURE_EMAIL=false
CRM_FEATURE_REPORTING=false

# Pipeline
CRM_ENABLE_ENRICHMENT=false
CRM_STRICT_VALIDATION=true
CRM_NOTIFY_ASSIGNMENT=true
```

## Extending the System

### Add a New Event
1. Create event class in `app/Events/`
2. Create listener class in `app/Listeners/`
3. Register in `AppServiceProvider::registerEventListeners()`
4. Dispatch from service using `event(new YourEvent($data))`

### Add a New Pipeline Stage
1. Create class in `app/Pipelines/LeadPipeline/`
2. Implement `handle(array $data, Closure $next): mixed`
3. Add to pipeline in service

### Add a New Service
1. Create interface in `app/Contracts/`
2. Create implementation in `app/Services/`
3. Add binding in `AppServiceProvider::$bindings`
4. Inject interface in controllers/consumers

## Documentation

- Full documentation: `docs/ENTERPRISE_ARCHITECTURE.md`
- This quick reference: `docs/QUICK_REFERENCE.md`
