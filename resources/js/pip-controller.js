/**
 * Picture-in-Picture Controller for SIP Audio Calls
 * 
 * Implements PiP functionality for audio-only SIP calls using:
 * - Media Session API for OS-level controls
 * - Canvas-based fake video stream for visual PiP window
 */

export class PipController {
    constructor() {
        this.pipWindow = null;
        this.canvas = null;
        this.ctx = null;
        this.videoElement = null;
        this.stream = null;
        this.animationFrameId = null;
        this.callInfo = {
            phoneNumber: '',
            direction: 'outbound',
            startTime: null,
            isMuted: false
        };
        
        this.initializeCanvas();
        this.initializeVideo();
        this.setupMediaSession();
    }

    initializeCanvas() {
        // Create a canvas for rendering the call UI
        this.canvas = document.createElement('canvas');
        this.canvas.width = 320;
        this.canvas.height = 180;
        this.ctx = this.canvas.getContext('2d');
    }

    initializeVideo() {
        // Create a video element to hold the canvas stream
        this.videoElement = document.createElement('video');
        this.videoElement.muted = true; // Mute the video element (audio comes from separate audio element)
        this.videoElement.setAttribute('playsinline', '');
        this.videoElement.style.display = 'none';
        document.body.appendChild(this.videoElement);
    }

    setupMediaSession() {
        if (!('mediaSession' in navigator)) {
            console.warn('Media Session API not supported');
            return;
        }

        // Set up Media Session metadata
        navigator.mediaSession.metadata = new MediaMetadata({
            title: 'On Call',
            artist: 'SIP Webphone',
            album: 'VoIP Call'
        });

        // Note: Action handlers will be set when callbacks are provided
    }

    setActionHandlers(handlers) {
        if (!('mediaSession' in navigator)) {
            return;
        }

        // Wire up call control actions
        if (handlers.hangup) {
            navigator.mediaSession.setActionHandler('hangup', handlers.hangup);
        }

        if (handlers.mute) {
            navigator.mediaSession.setActionHandler('pause', handlers.mute);
        }

        if (handlers.unmute) {
            navigator.mediaSession.setActionHandler('play', handlers.unmute);
        }
    }

    async startPip(callData, actionHandlers) {
        // Update call info
        this.callInfo = {
            phoneNumber: callData.phoneNumber || 'Unknown',
            direction: callData.direction || 'outbound',
            startTime: Date.now(),
            isMuted: false
        };

        // Set up action handlers
        if (actionHandlers) {
            this.setActionHandlers(actionHandlers);
        }

        // Start canvas rendering
        this.startCanvasRendering();

        // Capture canvas as video stream at 1 FPS (sufficient for timer updates)
        this.stream = this.canvas.captureStream(1);
        this.videoElement.srcObject = this.stream;

        try {
            // Play the video (required before requesting PiP)
            await this.videoElement.play();

            // Request Picture-in-Picture
            if (document.pictureInPictureEnabled) {
                this.pipWindow = await this.videoElement.requestPictureInPicture();

                this.videoElement.addEventListener('leavepictureinpicture', () => {
                    this.handlePipClosed();
                });

                console.log('Picture-in-Picture started');
            } else {
                console.warn('Picture-in-Picture not supported');
            }
        } catch (error) {
            console.error('Failed to start Picture-in-Picture:', error);
        }
    }

    async stopPip() {
        try {
            if (document.pictureInPictureElement) {
                await document.exitPictureInPicture();
            }
        } catch (error) {
            console.error('Failed to stop Picture-in-Picture:', error);
        }

        this.cleanup();
    }

    handlePipClosed() {
        console.log('Picture-in-Picture window closed');
        this.pipWindow = null;
    }

    startCanvasRendering() {
        // Render at 1 FPS to match stream capture rate
        const renderInterval = 1000; // 1 second
        
        const render = () => {
            this.drawCallUI();
        };
        
        // Initial render
        render();
        
        // Set interval for subsequent renders at 1 FPS
        this.animationFrameId = setInterval(render, renderInterval);
    }

    stopCanvasRendering() {
        if (this.animationFrameId) {
            clearInterval(this.animationFrameId);
            this.animationFrameId = null;
        }
    }

    drawCallUI() {
        const width = this.canvas.width;
        const height = this.canvas.height;

        // Clear canvas
        this.ctx.clearRect(0, 0, width, height);

        // Background gradient
        const gradient = this.ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, '#4f46e5'); // Indigo-600
        gradient.addColorStop(1, '#3730a3'); // Indigo-800
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, width, height);

        // Draw avatar circle
        const avatarRadius = 30;
        const avatarX = width / 2;
        const avatarY = 50;
        
        this.ctx.beginPath();
        this.ctx.arc(avatarX, avatarY, avatarRadius, 0, Math.PI * 2);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();

        // Draw user icon in avatar
        this.ctx.fillStyle = '#4f46e5';
        this.ctx.font = 'bold 24px Arial';
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';
        this.ctx.fillText('📞', avatarX, avatarY);

        // Draw phone number
        this.ctx.fillStyle = '#ffffff';
        this.ctx.font = 'bold 18px Arial';
        this.ctx.textAlign = 'center';
        this.ctx.fillText(this.callInfo.phoneNumber, width / 2, 100);

        // Draw call direction
        this.ctx.font = '12px Arial';
        const directionText = this.callInfo.direction === 'inbound' ? 'Incoming Call' : 'Outgoing Call';
        this.ctx.fillText(directionText, width / 2, 120);

        // Draw call timer
        if (this.callInfo.startTime) {
            const elapsed = Math.floor((Date.now() - this.callInfo.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            const timeStr = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            this.ctx.font = 'bold 20px monospace';
            this.ctx.fillText(timeStr, width / 2, 145);
        }

        // Draw mute indicator if muted
        if (this.callInfo.isMuted) {
            this.ctx.fillStyle = '#ef4444'; // Red
            this.ctx.font = 'bold 14px Arial';
            this.ctx.fillText('🔇 MUTED', width / 2, 165);
        } else {
            this.ctx.fillStyle = '#10b981'; // Green
            this.ctx.font = 'bold 14px Arial';
            this.ctx.fillText('🎤 ACTIVE', width / 2, 165);
        }
    }

    updateCallInfo(updates) {
        this.callInfo = { ...this.callInfo, ...updates };
    }

    setMuted(muted) {
        this.callInfo.isMuted = muted;
    }

    cleanup() {
        this.stopCanvasRendering();

        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }

        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = null;
            navigator.mediaSession.setActionHandler('hangup', null);
            navigator.mediaSession.setActionHandler('pause', null);
            navigator.mediaSession.setActionHandler('play', null);
        }

        this.pipWindow = null;
        this.callInfo = {
            phoneNumber: '',
            direction: 'outbound',
            startTime: null,
            isMuted: false
        };
    }

    destroy() {
        this.cleanup();

        if (this.videoElement) {
            this.videoElement.remove();
            this.videoElement = null;
        }

        this.canvas = null;
        this.ctx = null;
    }
}
