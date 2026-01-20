# Agent Console - WebRTC Telephony Integration

## Overview

The Agent Console provides a complete WebRTC telephony solution for CRM agents using SIP.js. It enables agents to make and receive calls directly from their browser, with full integration into the CRM system.

## Features

### Core Functionality
- ✅ **SIP Registration**: Automatic registration when agent status is set to "Available"
- ✅ **Outbound Calls**: Make calls via dial pad or click-to-call
- ✅ **Inbound Calls**: Receive and answer incoming calls
- ✅ **Call Controls**: Mute, hold, hangup, DTMF
- ✅ **Call Timer**: Real-time call duration tracking
- ✅ **Recent Calls**: Display last 10 calls with details
- ✅ **Lead Linking**: Automatic linking to leads based on phone number
- ✅ **Call Wrap-Up**: Integrated call disposition and notes
- ✅ **Audit Logging**: Comprehensive logging of all telephony events

### Agent Status Management
- **Offline**: No SIP registration, cannot receive calls
- **Available**: SIP registered, can make and receive calls
- **Away**: SIP unregistered, cannot receive calls (logged in but unavailable)

### Click-to-Call Integration
The system uses the **BroadcastChannel API** for communication between pages and the Agent Console:

#### Channel: `crm-agent-phone`

#### Message Types:

**CALL_REQUEST** (sent from any page)
```javascript
{
  type: "CALL_REQUEST",
  number: "+15551234567",
  relatedType: "lead",
  relatedId: 123,
  requestId: "uuid",
  timestamp: 1234567890
}
```

**CALL_STARTED** (sent from Agent Console)
```javascript
{
  type: "CALL_STARTED",
  number: "+15551234567",
  direction: "outbound",
  timestamp: 1234567890
}
```

**CALL_ANSWERED** (sent from Agent Console)
```javascript
{
  type: "CALL_ANSWERED",
  number: "+15551234567",
  timestamp: 1234567890
}
```

**CALL_ENDED** (sent from Agent Console)
```javascript
{
  type: "CALL_ENDED",
  duration_seconds: 120,
  timestamp: 1234567890
}
```

**CALL_FAILED** (sent from Agent Console)
```javascript
{
  type: "CALL_FAILED",
  reason: "Connection failed",
  timestamp: 1234567890
}
```

## Usage

### Accessing the Agent Console

1. Navigate to `/agent/console`
2. Requires role: `agent`, `supervisor`, or `tenant_admin`
3. Requires SIP credentials to be configured by administrator

### Making Calls

#### From Dial Pad
1. Set status to "Available"
2. Enter phone number in dial pad
3. Click "Call" button

#### From Lead Page (Click-to-Call)
```javascript
// Simple usage
clickToCall('+15551234567');

// With lead context
clickToCall('+15551234567', 'lead', 123);
```

#### From Blade Template
```blade
<button onclick="clickToCall('{{ $lead->phone }}', 'lead', {{ $lead->id }}')">
    Call Lead
</button>
```

#### With Alpine.js
```blade
<button @click="$dispatch('click-to-call', { 
    number: '{{ $lead->phone }}', 
    relatedType: 'lead', 
    relatedId: {{ $lead->id }} 
})">
    Call Lead
</button>
```

#### From Livewire Component
```php
// In your Livewire component
$this->dispatch('click-to-call', [
    'number' => $this->lead->phone,
    'relatedType' => 'lead',
    'relatedId' => $this->lead->id,
]);
```

### Receiving Calls

1. Set status to "Available"
2. Wait for incoming call notification
3. Click "Answer" button in the active call panel

### Call Controls

- **Mute/Unmute**: Toggle microphone on/off
- **Hold/Resume**: Place call on hold
- **Hangup**: End the current call
- **DTMF**: Send touch-tone digits during call

## Technical Architecture

### Frontend Components

1. **Livewire Component**: `App\Livewire\Agent\Console`
   - Agent status management
   - Call state tracking
   - Database integration
   - Event handling

2. **JavaScript Module**: `resources/js/sip-agent.js`
   - SIP.js UserAgent initialization
   - WebRTC session management
   - Audio handling
   - BroadcastChannel integration

3. **Click-to-Call Helper**: `resources/js/click-to-call.js`
   - Global `clickToCall()` function
   - Alpine.js directive
   - Livewire event listener
   - Notification system

### Backend Components

1. **Routes**:
   - `GET /agent/console` - Main console page
   - `POST /agent/console/sip-password` - Secure SIP password retrieval

2. **Models**:
   - `AgentSipCredential` - SIP account configuration
   - `Call` - Call records
   - `AuditLog` - Activity logging

### Security

1. **SIP Password Protection**:
   - Stored encrypted in database
   - Retrieved via secure endpoint with authentication
   - Never exposed in JavaScript source

2. **Tenant Isolation**:
   - All queries scoped to current tenant
   - SIP credentials per tenant/user
   - Call records linked to tenant

3. **Audit Logging**:
   - All SIP registration events
   - All call attempts and outcomes
   - Agent status changes
   - IP address and user agent tracking

## Configuration

### SIP Credentials Setup

SIP credentials must be configured by an administrator for each agent:

```php
AgentSipCredential::create([
    'tenant_id' => $tenant->id,
    'user_id' => $user->id,
    'sip_ws_url' => 'wss://pbx.example.com:7443',
    'sip_username' => '1001',
    'sip_password' => 'secret', // Automatically encrypted
    'sip_domain' => 'pbx.example.com',
    'display_name' => 'John Doe',
    'auto_register' => false,
]);
```

### Required Permissions

- Agent Console access: `agent`, `supervisor`, or `tenant_admin` role
- Navigation menu item shown only for authorized roles

## Browser Requirements

- Modern browser with WebRTC support
- Microphone access permission
- BroadcastChannel API support (all modern browsers)
- Secure context (HTTPS) for WebRTC

## Call Flow

### Outbound Call Flow
1. Agent clicks "Call" or uses click-to-call
2. SIP.js creates Inviter session
3. Call record created in database
4. Auto-link to Lead if phone matches
5. Call timer starts
6. Agent can use call controls
7. On hangup, call duration recorded
8. Call wrap-up modal appears
9. Agent selects disposition and adds notes

### Inbound Call Flow
1. SIP.js receives INVITE
2. Call record created in database
3. Auto-link to Lead if phone matches
4. Agent sees incoming call notification
5. Agent clicks "Answer"
6. Call timer starts
7. Agent can use call controls
8. On hangup, call duration recorded
9. Call wrap-up modal appears
10. Agent selects disposition and adds notes

## Troubleshooting

### SIP Registration Fails
- Check SIP credentials are correct
- Verify WebSocket URL is accessible
- Check firewall allows WebSocket connections
- Ensure agent status is "Available"

### No Audio
- Check microphone permissions in browser
- Verify audio output device selected
- Check browser console for WebRTC errors
- Test with simple audio playback

### Click-to-Call Not Working
- Verify Agent Console is open in another tab
- Check BroadcastChannel is supported
- Check browser console for errors
- Ensure agent status is "Available"

### Call Quality Issues
- Check network connection quality
- Verify bandwidth is sufficient
- Check for packet loss
- Consider using wired connection

## Development

### Adding New Call Controls

1. Add UI button in `console.blade.php`
2. Add Livewire action in `Console.php`
3. Add SIP.js method in `sip-agent.js`
4. Wire up Livewire event listener

### Extending Click-to-Call

1. Add new message type to `click-to-call.js`
2. Handle message in `sip-agent.js`
3. Update documentation

### Custom Call Disposition

1. Add disposition in `CallDisposition` model
2. Configure in settings page
3. Appears automatically in wrap-up modal

## API Reference

### Global Functions

#### `clickToCall(number, relatedType?, relatedId?, requestId?)`
Initiates a call from anywhere in the application.

**Parameters:**
- `number` (string): Phone number to call
- `relatedType` (string, optional): Related entity type ('lead', 'contact')
- `relatedId` (number, optional): Related entity ID
- `requestId` (string, optional): Unique request identifier

**Returns:** boolean - true if request sent, false otherwise

### Livewire Events

#### Dispatched TO Agent Console
- `registerSip`: Register SIP user agent
- `unregisterSip`: Unregister SIP user agent
- `makeOutboundCall`: Make outbound call
- `answerInboundCall`: Answer incoming call
- `hangupCall`: Hangup active call
- `toggleMute`: Toggle microphone mute
- `toggleHold`: Toggle call hold
- `sendDtmf`: Send DTMF tone

#### Dispatched FROM Agent Console
- `sipRegistered`: SIP registration successful
- `sipUnregistered`: SIP unregistered
- `sipRegistrationFailed`: SIP registration failed
- `callStarted`: Call started
- `callAnswered`: Call answered
- `callEnded`: Call ended
- `callFailed`: Call failed
- `callTimerUpdate`: Call timer tick
- `showCallWrapUpModal`: Show wrap-up modal

## Future Enhancements

- [ ] Call transfer functionality
- [ ] Conference calling
- [ ] Call recording
- [ ] Call queues
- [ ] IVR integration
- [ ] Call analytics dashboard
- [ ] WebRTC call quality metrics
- [ ] Screen pop on incoming calls
- [ ] Call whisper/barge features
- [ ] Voicemail integration

## References

- [SIP.js Documentation](https://sipjs.com/)
- [WebRTC API](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)
- [BroadcastChannel API](https://developer.mozilla.org/en-US/docs/Web/API/Broadcast_Channel_API)
