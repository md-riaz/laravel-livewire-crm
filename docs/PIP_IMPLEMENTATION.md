# Picture-in-Picture Implementation for SIP Calling

## Overview

This implementation adds Picture-in-Picture (PiP) functionality to the SIP audio calling feature in the Laravel Livewire CRM application. Since audio-only applications cannot directly use the browser's PiP API (which is designed for video), this implementation uses a canvas-based approach to create a visual representation of the call.

## Features

### 1. Media Session API Integration
- **OS-level call controls**: The application registers with the browser's Media Session API to provide native call controls
- **Lock screen controls**: Users can control calls from their device's lock screen
- **Chrome toolbar integration**: Call controls appear in Chrome's toolbar media controls
- **Priority handling**: The browser treats the application as a first-class call application

### 2. Canvas-based PiP Window
- **Visual call interface**: A canvas element renders a visual UI showing:
  - Phone number or caller ID
  - Call direction (inbound/outbound)
  - Call timer (real-time)
  - Mute status indicator
  - Visual avatar/icon
- **Real-time updates**: The canvas refreshes at 30 FPS to show live call information
- **Automatic activation**: PiP window launches automatically when a call is established

### 3. Cross-tab Audio Continuity
- **Persistent audio element**: Audio streams are attached to a persistent `<audio>` element
- **SPA-safe navigation**: Audio continues playing during client-side navigation
- **Background operation**: Calls can continue while users switch tabs or windows

## Architecture

### Components

1. **PipController** (`resources/js/pip-controller.js`)
   - Manages the PiP lifecycle
   - Renders call UI on canvas
   - Handles Media Session API
   - Provides action handlers for call controls

2. **SipAgent** (`resources/js/sip-agent.js`)
   - Integrates PipController into the SIP call flow
   - Triggers PiP on call establishment
   - Synchronizes call state with PiP UI
   - Cleans up PiP on call termination

### Flow

```
Call Established
    ↓
setupRemoteMedia()
    ↓
startPictureInPicture()
    ↓
PipController.startPip()
    ↓
- Initialize canvas rendering
- Capture canvas as video stream
- Request PiP on video element
- Set up Media Session handlers
    ↓
PiP Window Active
    ↓
Call Ends
    ↓
cleanupSession()
    ↓
PipController.stopPip()
```

## Usage

### For Developers

The PiP functionality is automatically integrated into the SIP calling workflow. No additional configuration is required beyond the existing SIP setup.

### For Users

1. **Starting a Call**: When you make or receive a call, the PiP window will automatically appear
2. **Using PiP Controls**: You can:
   - Minimize the browser and keep the call visible in the PiP window
   - Switch tabs while maintaining the call
   - Use OS-level media controls to hang up or mute
3. **Closing PiP**: The PiP window automatically closes when the call ends

## Browser Compatibility

- **Chrome/Edge**: Full support for PiP and Media Session API
- **Firefox**: Supports PiP, limited Media Session API support
- **Safari**: Supports PiP, limited Media Session API support
- **Mobile browsers**: Media Session API provides lock-screen controls

## Technical Details

### Canvas Rendering

The canvas is rendered at 320x180 pixels (16:9 aspect ratio) and captures at 30 FPS. The visual design includes:

- **Background**: Gradient from indigo-600 to indigo-800
- **Avatar circle**: White circle with phone emoji
- **Phone number**: Bold white text
- **Call direction**: Small white text
- **Timer**: Monospace font showing MM:SS format
- **Status indicator**: Shows "ACTIVE" (green) or "MUTED" (red)

### Media Session Actions

The following actions are registered:
- `hangup`: Terminates the call
- `pause`: Mutes the microphone
- `play`: Unmutes the microphone

### Audio Handling

Audio is managed through:
- A persistent `<audio>` element with `autoplay` attribute
- Remote stream from WebRTC peer connection
- Separate from the video stream used for PiP

## Workaround Explanation

This implementation follows the "canvas trick" approach recommended for audio-only PiP:

1. **Why not direct audio PiP?**: Browsers only support PiP for `<video>` elements, not `<audio>` elements
2. **The solution**: Create a fake video surface by:
   - Drawing a UI on a canvas
   - Capturing the canvas as a video stream
   - Feeding it to a `<video>` element
   - Requesting PiP on that video element
3. **Audio separation**: The actual call audio continues through the `<audio>` element, while the PiP window shows the visual UI

This approach is:
- 100% specification-compliant
- Widely used in professional WebRTC applications
- The standard method for implementing "audio-only PiP"

## Limitations

Due to browser security and API constraints:
- PiP requires a user gesture (automatic launch happens after call connection)
- Cannot auto-pin UI on new tab pages
- Cannot merge call UI into the browser chrome
- Special browser features (like Google Meet's) are not available to third-party apps

## Future Enhancements

Possible improvements:
- Custom PiP controls (if browser support improves)
- Call transfer button in PiP window
- Contact photo/avatar display
- Call notes or lead information
- Multiple call handling UI
