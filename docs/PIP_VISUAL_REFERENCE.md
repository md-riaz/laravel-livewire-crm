# Picture-in-Picture Visual Reference

## PiP Window Appearance

The Picture-in-Picture window displays the following elements:

```
┌─────────────────────────────────┐
│   PiP Window (320×180 pixels)   │
│  ┌────────────────────────────┐ │
│  │                            │ │
│  │         ┌─────┐            │ │
│  │         │  📞 │            │ │  ← Avatar (white circle with phone emoji)
│  │         └─────┘            │ │
│  │                            │ │
│  │     +1-555-1234            │ │  ← Phone Number (bold, white)
│  │    Outgoing Call           │ │  ← Direction (small, white)
│  │       01:23                │ │  ← Timer (monospace, white)
│  │    🎤 ACTIVE               │ │  ← Status (green when active)
│  │                            │ │
│  │  Background: Indigo        │ │
│  │  Gradient (#4f46e5 →      │ │
│  │            #3730a3)        │ │
│  └────────────────────────────┘ │
└─────────────────────────────────┘
```

## Status Indicators

### Active Call
```
🎤 ACTIVE
(Green text: #10b981)
```

### Muted Call
```
🔇 MUTED
(Red text: #ef4444)
```

## PiP Window States

### 1. Outgoing Call
- Direction: "Outgoing Call"
- Shows dialed number
- Timer starts when call connects

### 2. Incoming Call
- Direction: "Incoming Call"
- Shows caller's number
- Timer starts when answered

### 3. During Call
- Timer updates every second (MM:SS format)
- Status changes based on mute state
- Floating window can be moved anywhere on screen

## Browser Integration

### Chrome Toolbar
```
┌────────────────────────────────────┐
│ Chrome Toolbar                     │
│ [🔊] On Call - SIP Webphone       │  ← Media control icon
│      [⏸️ Pause] [⏹️ Stop]          │  ← Mute/Hangup buttons
└────────────────────────────────────┘
```

### Lock Screen (Mobile)
```
┌────────────────────────────────────┐
│ Lock Screen                        │
│                                    │
│    🎵 On Call                      │
│    SIP Webphone                    │
│    VoIP Call                       │
│                                    │
│    [▶️] [⏸️] [⏹️]                   │  ← Play/Pause/Stop
└────────────────────────────────────┘
```

## PiP Window Features

### Movable
- Can be dragged to any screen position
- Stays on top of other windows
- Can be resized (within browser limits)

### Persistent
- Remains visible when switching tabs
- Stays visible when browser is minimized
- Closes automatically when call ends

### Interactive
- Shows real-time updates
- Timer updates every second
- Status changes with mute state
- Visual feedback for all actions

## Color Scheme

```
Background Gradient:
  Top:    #4f46e5 (Indigo-600)
  Bottom: #3730a3 (Indigo-800)

Text Colors:
  Primary:    #ffffff (White)
  Active:     #10b981 (Green-500)
  Muted:      #ef4444 (Red-500)

Avatar:
  Circle:     #ffffff (White)
  Icon:       #4f46e5 (Indigo-600)
  Background: #4f46e5 (Indigo-600)
```

## Typography

```
Phone Number:  bold 18px Arial
Direction:     12px Arial
Timer:         bold 20px monospace
Status:        bold 14px Arial
```

## Animation

- Timer updates: 1 FPS (every second)
- Smooth transitions: CSS handled by browser
- No flicker: Double-buffered canvas
- Low CPU: Optimized rendering

## Dimensions

```
Canvas Size:    320×180 pixels (16:9 aspect ratio)
Avatar Radius:  30 pixels
Padding:        Standard spacing
```

## Browser Controls

When PiP is active, users can:
1. **Move the window** - Click and drag
2. **Resize** (if browser supports) - Drag corners
3. **Close** - Click X button (doesn't end call)
4. **Return to tab** - Click picture icon

## Example Scenarios

### Scenario 1: Making a Call
```
Initial State:
  Direction: Outgoing Call
  Number: +1-555-1234
  Timer: 00:00
  Status: 🎤 ACTIVE

After 1 minute:
  Direction: Outgoing Call
  Number: +1-555-1234
  Timer: 01:00
  Status: 🎤 ACTIVE
```

### Scenario 2: Muting During Call
```
Before Mute:
  Status: 🎤 ACTIVE (green)

After Mute:
  Status: 🔇 MUTED (red)
```

### Scenario 3: Receiving a Call
```
Incoming Call:
  Direction: Incoming Call
  Number: +1-555-9876
  Timer: 00:00
  Status: 🎤 ACTIVE
```

## Media Session Integration

### Metadata
```javascript
{
  title: 'On Call',
  artist: 'SIP Webphone',
  album: 'VoIP Call'
}
```

### Actions
- **Hangup:** Ends the call
- **Pause/Mute:** Mutes microphone
- **Play/Unmute:** Unmutes microphone

## User Benefits

1. **Always Visible**: See call status anywhere
2. **Tab Freedom**: Switch tabs without losing call info
3. **Multitask**: Work while on call
4. **Quick Controls**: OS-level mute/hangup
5. **Professional**: Desktop-grade experience

## Technical Notes

- **Render Method:** HTML5 Canvas
- **Capture Method:** `captureStream(1)` at 1 FPS
- **Display Method:** Picture-in-Picture API
- **Audio Method:** Separate `<audio>` element
- **Update Frequency:** 1 Hz (once per second)

## Accessibility

- High contrast colors
- Large, readable text
- Clear status indicators
- Emoji for visual recognition
- Color + icon for status (not color-only)

## Performance Impact

- CPU: < 2%
- Memory: ~5-10 MB
- Network: 0 (no streaming)
- Battery: Minimal impact
- Render: 1 FPS (efficient)

---

**Note:** The actual PiP window appearance may vary slightly between browsers (Chrome, Edge, Firefox, Safari) but the core content and layout remain consistent.
