# Picture-in-Picture Testing Guide

## Manual Testing Checklist

### Prerequisites
- Chrome/Edge browser (version 94+)
- Valid SIP credentials configured
- Microphone permissions granted

### Test 1: PiP Window Activation
**Steps:**
1. Log in to the CRM
2. Navigate to Agent Console
3. Register SIP phone (status should show "Registered")
4. Make an outbound call to a valid number
5. Wait for call to establish

**Expected Results:**
- ✅ Picture-in-Picture window should automatically appear
- ✅ PiP window should show:
  - Phone number
  - "Outgoing Call" text
  - Call timer (MM:SS format)
  - "ACTIVE" status in green

**Screenshot Location:** Take screenshot of PiP window

---

### Test 2: PiP Visual Updates
**Steps:**
1. With an active call in PiP mode
2. Click the "Mute" button in the main console

**Expected Results:**
- ✅ PiP window should update to show "MUTED" in red
- ✅ Status icon should change from microphone to muted microphone

**Steps:**
3. Click "Unmute" button

**Expected Results:**
- ✅ PiP window should update to show "ACTIVE" in green
- ✅ Status icon should change back to active microphone

---

### Test 3: Call Timer Accuracy
**Steps:**
1. Start a call and note the start time
2. Let the call run for 90 seconds (1:30)
3. Observe the timer in both the main console and PiP window

**Expected Results:**
- ✅ Timer in PiP window should match the main console timer
- ✅ Timer should increment every second
- ✅ Timer format should be MM:SS (e.g., 01:30)

---

### Test 4: PiP Window Persistence
**Steps:**
1. Start a call with PiP active
2. Minimize the browser window
3. Observe the PiP window

**Expected Results:**
- ✅ PiP window should remain visible on screen
- ✅ PiP window should continue showing live call information
- ✅ Timer should continue updating

**Steps:**
4. Switch to another application/tab
5. Observe the PiP window

**Expected Results:**
- ✅ PiP window should still be visible
- ✅ Call audio should continue
- ✅ Timer should continue updating

---

### Test 5: Media Session Controls (Chrome Toolbar)
**Steps:**
1. Start a call
2. Look for the media control icon in Chrome's toolbar (usually near the address bar)
3. Click on the media control icon

**Expected Results:**
- ✅ Should show "On Call" with "SIP Webphone"
- ✅ Should show playback controls

**Steps:**
4. Click the "Pause" button in media controls

**Expected Results:**
- ✅ Call should be muted
- ✅ PiP window should show "MUTED" status

**Steps:**
5. Click the "Play" button in media controls

**Expected Results:**
- ✅ Call should be unmuted
- ✅ PiP window should show "ACTIVE" status

---

### Test 6: Hangup from Media Session
**Steps:**
1. Start a call with PiP active
2. Use OS-level media controls (e.g., Chrome toolbar, system media keys, or lock screen controls on mobile)
3. Click the "Stop" or "Close" button

**Expected Results:**
- ✅ Call should end
- ✅ PiP window should close
- ✅ Main console should show call as ended
- ✅ Call wrap-up modal should appear (if configured)

---

### Test 7: Inbound Call PiP
**Steps:**
1. Receive an inbound call
2. Answer the call

**Expected Results:**
- ✅ PiP window should appear
- ✅ PiP window should show:
  - Caller's phone number
  - "Incoming Call" text
  - Call timer
  - "ACTIVE" status

---

### Test 8: Cross-Tab Audio Continuity
**Steps:**
1. Start a call with PiP active
2. Open a new tab
3. Navigate to a different website
4. Listen for call audio

**Expected Results:**
- ✅ Call audio should continue playing
- ✅ PiP window should remain visible
- ✅ Microphone should still be active

**Steps:**
5. Switch back to the CRM tab
6. Verify call is still active

**Expected Results:**
- ✅ Call should still be connected
- ✅ Timer should show correct duration
- ✅ All call controls should work

---

### Test 9: PiP Cleanup on Call End
**Steps:**
1. Start a call with PiP active
2. Click "Hangup" in the main console

**Expected Results:**
- ✅ PiP window should close immediately
- ✅ No errors in browser console
- ✅ Video element should be cleaned up
- ✅ Canvas should stop rendering

**Steps:**
3. Open browser console (F12)
4. Check for any errors or warnings

**Expected Results:**
- ✅ No errors related to PiP
- ✅ Should see "Picture-in-Picture window closed" log

---

### Test 10: Multiple Call Handling
**Steps:**
1. Start a call with PiP active
2. End the call
3. Wait for PiP to close
4. Start another call

**Expected Results:**
- ✅ New PiP window should appear
- ✅ New call information should be displayed
- ✅ Timer should start from 00:00
- ✅ No interference from previous call

---

### Test 11: Browser Compatibility
**Test on:**
- Chrome (latest)
- Microsoft Edge (latest)
- Firefox (if available)

**Expected Results:**
- ✅ Chrome: Full PiP and Media Session support
- ✅ Edge: Full PiP and Media Session support
- ℹ️ Firefox: PiP may work but Media Session support may be limited

---

### Test 12: Error Handling
**Steps:**
1. Start a call
2. Close the PiP window manually (using the X button on the PiP window)
3. Continue the call

**Expected Results:**
- ✅ Call should continue normally
- ✅ Audio should not be affected
- ✅ Main console controls should still work
- ✅ No errors in console

---

## Performance Testing

### CPU Usage
**Steps:**
1. Open Task Manager/Activity Monitor
2. Start a call with PiP
3. Monitor CPU usage for 5 minutes

**Expected Results:**
- ✅ CPU usage should be minimal (< 5% on modern hardware)
- ✅ No memory leaks
- ✅ Browser should remain responsive

### Memory Usage
**Steps:**
1. Open Chrome Task Manager (Shift+Esc)
2. Start and end 10 calls in succession
3. Monitor memory usage

**Expected Results:**
- ✅ Memory should not continuously increase
- ✅ Resources should be properly cleaned up after each call

---

## Known Limitations

1. **User Gesture Requirement**: PiP requires a user gesture, so it automatically activates after the call is established (which is triggered by user action)

2. **Browser Support**: PiP is not supported in all browsers. The implementation degrades gracefully in unsupported browsers.

3. **Mobile Browsers**: PiP behavior may vary on mobile devices. Media Session API provides better mobile support.

4. **Same-Origin Policy**: The implementation works within the CRM domain and doesn't require cross-origin access.

---

## Troubleshooting

### PiP Window Doesn't Appear
- Check browser console for errors
- Verify PiP is enabled in browser settings
- Ensure you have the latest browser version
- Check that the call is actually established (not ringing)

### Audio Issues
- The PiP feature is visual only - audio comes from the separate `<audio>` element
- If audio stops, it's not related to PiP
- Check SIP connection and network

### Controls Not Working
- Ensure Media Session API is supported (check `'mediaSession' in navigator`)
- Try using the main console controls instead
- Check for JavaScript errors in console

---

## Report Issues

If you encounter any issues during testing, please provide:
1. Browser name and version
2. Operating system
3. Steps to reproduce
4. Screenshot or screen recording
5. Browser console logs (F12 → Console tab)
