# CallWrapUpModal Component Usage Guide

## Overview
The `CallWrapUpModal` is a Livewire component that provides mandatory call wrap-up functionality for the multi-tenant CRM. This modal appears automatically when a call ends and cannot be dismissed until the wrap-up is completed.

## Files
- Component: `app/Livewire/Calls/CallWrapUpModal.php`
- View: `resources/views/livewire/calls/call-wrap-up-modal.blade.php`

## Features
1. **Mandatory Wrap-Up**: Modal cannot be closed without completing the form
2. **Call Disposition**: Required dropdown selection from tenant-specific dispositions
3. **Conditional Notes**: Notes become required when disposition has `requires_note = true`
4. **Follow-up Scheduling**: Optional date picker for scheduling next follow-up (lead calls only)
5. **Call Information Display**: Shows call direction, duration, phone number, and related lead
6. **Lead Activity Tracking**: Automatically creates activity record for lead-related calls
7. **Event-Driven**: Integrates with Livewire event system

## Usage

### 1. Include the Component in Your Layout
Add the component to your main layout or page where calls occur:

```blade
<livewire:calls.call-wrap-up-modal />
```

### 2. Trigger the Modal
Dispatch the `showCallWrapUpModal` event when a call ends:

```javascript
// From JavaScript
Livewire.dispatch('showCallWrapUpModal', { callId: 123 });

// Or from another Livewire component
$this->dispatch('showCallWrapUpModal', callId: $call->id);
```

### 3. Listen for Completion
The component dispatches `callWrappedUp` event after successful save:

```php
// In another Livewire component
#[On('callWrappedUp')]
public function handleCallWrappedUp()
{
    // Refresh call list, update UI, etc.
    $this->refreshCalls();
}
```

```javascript
// In JavaScript
document.addEventListener('livewire:initialized', () => {
    Livewire.on('callWrappedUp', () => {
        // Update UI, show notification, etc.
        console.log('Call wrap-up completed');
    });
});
```

## Component Properties

### Public Properties
- `$show` (bool): Controls modal visibility
- `$callId` (int|null): ID of the call being wrapped up
- `$call` (Call|null): The Call model instance
- `$disposition_id` (int): Selected disposition ID (required)
- `$wrapup_notes` (string): Call notes (conditionally required)
- `$schedule_followup` (bool): Whether to schedule a follow-up
- `$followup_date` (string): Date for next follow-up

### Computed Properties
- `$this->isLeadCall`: Returns true if call is related to a lead

## Validation Rules

### Always Required
- `disposition_id`: Must exist in call_dispositions table

### Conditionally Required
- `wrapup_notes`: Required (min 3 chars) if selected disposition has `requires_note = true`
- `followup_date`: Required, must be a date after today if `schedule_followup` is true

## Database Updates

### Call Model
Updates the following fields:
- `disposition_id`: Selected disposition
- `wrapup_notes`: Call notes

### Lead Model (if call is related to a lead)
Updates the following fields:
- `last_contacted_at`: Set to current timestamp
- `next_followup_at`: Set to selected date (only if scheduling follow-up)

### LeadActivity Model (if call is related to a lead)
Creates a new activity record with:
- `lead_id`: Related lead ID
- `user_id`: Current user ID
- `type`: 'call'
- `payload_json`: Contains call details (call_id, direction, duration, disposition, notes)

## Example Integration

### In a Call Management Page

```blade
<!-- Your call interface -->
<div>
    <button wire:click="endCall({{ $call->id }})">
        End Call
    </button>
</div>

<!-- Include the modal component -->
<livewire:calls.call-wrap-up-modal />

@push('scripts')
<script>
    // Listen for call end event
    Livewire.on('callEnded', (callId) => {
        // Trigger wrap-up modal
        Livewire.dispatch('showCallWrapUpModal', { callId: callId });
    });
    
    // Handle completion
    Livewire.on('callWrappedUp', () => {
        // Refresh call list or redirect
        window.location.reload();
    });
</script>
@endpush
```

### In a Livewire Component

```php
use Livewire\Attributes\On;
use Livewire\Component;

class CallManager extends Component
{
    public function endCall($callId)
    {
        // End the call logic...
        
        // Trigger wrap-up modal
        $this->dispatch('showCallWrapUpModal', callId: $callId);
    }
    
    #[On('callWrappedUp')]
    public function onCallWrappedUp()
    {
        // Refresh data
        $this->dispatch('$refresh');
        
        // Show success message
        session()->flash('message', 'Call completed successfully');
    }
}
```

## Styling and Customization

The component uses Tailwind CSS classes and can be customized by modifying the view file. Key styling areas:

- **Modal Backdrop**: `.bg-gray-500 bg-opacity-75`
- **Modal Container**: `.bg-white rounded-lg shadow-xl max-w-2xl`
- **Primary Button**: `.bg-blue-600 hover:bg-blue-700`
- **Animations**: Alpine.js x-transition directives

## Security Considerations

1. **Tenant Scoping**: All queries are automatically scoped to the current user's tenant via the `BelongsToTenant` trait
2. **Authorization**: The component relies on Laravel's authentication; ensure users are authenticated
3. **Validation**: All inputs are validated both client-side and server-side
4. **XSS Prevention**: Blade templating automatically escapes output

## Troubleshooting

### Modal doesn't appear
- Ensure the component is included in your view
- Check that the event name is spelled correctly: `showCallWrapUpModal`
- Verify the call ID being passed exists in the database

### Validation errors not showing
- Check that the disposition has `requires_note` set correctly in the database
- Ensure date picker has proper `min` attribute for follow-up dates

### Lead activity not being created
- Verify the call's `related_type` is set to `App\Models\Lead`
- Ensure the `related_id` points to a valid lead
- Check that the lead exists and belongs to the same tenant

## Database Requirements

Ensure these tables exist with the proper structure:
- `calls` - with columns: disposition_id, wrapup_notes
- `call_dispositions` - with columns: name, requires_note, sort_order
- `leads` - with columns: last_contacted_at, next_followup_at
- `lead_activities` - with columns: lead_id, user_id, type, payload_json

## Testing

### Manual Testing Checklist
- [ ] Modal appears when event is dispatched
- [ ] Modal cannot be closed without completing form
- [ ] Disposition dropdown loads tenant dispositions
- [ ] Notes field becomes required for dispositions with `requires_note = true`
- [ ] Follow-up section only appears for lead-related calls
- [ ] Date picker validates dates are after today
- [ ] Call information displays correctly
- [ ] Save button shows loading state
- [ ] Success message appears after save
- [ ] `callWrappedUp` event is dispatched
- [ ] Lead activity is created for lead calls
- [ ] Lead timestamps are updated correctly

### Unit Testing Example

```php
use App\Livewire\Calls\CallWrapUpModal;
use App\Models\Call;
use App\Models\CallDisposition;
use App\Models\Lead;
use Livewire\Livewire;

test('can complete call wrap-up', function () {
    $user = User::factory()->create();
    $disposition = CallDisposition::factory()->create([
        'tenant_id' => $user->tenant_id,
        'requires_note' => false,
    ]);
    $call = Call::factory()->create([
        'tenant_id' => $user->tenant_id,
        'user_id' => $user->id,
    ]);
    
    Livewire::actingAs($user)
        ->test(CallWrapUpModal::class)
        ->call('showModal', $call->id)
        ->set('disposition_id', $disposition->id)
        ->call('save')
        ->assertDispatched('callWrappedUp');
    
    expect($call->fresh()->disposition_id)->toBe($disposition->id);
});
```

## Support

For issues or questions about this component, refer to:
- Laravel Livewire documentation: https://livewire.laravel.com/
- Alpine.js documentation: https://alpinejs.dev/
- Tailwind CSS documentation: https://tailwindcss.com/

