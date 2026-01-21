import { UserAgent, Registerer, Inviter, SessionState } from 'sip.js';
import { PipController } from './pip-controller.js';

export class SipAgent {
    constructor(config, livewireComponent) {
        this.config = config;
        this.livewire = livewireComponent;
        this.userAgent = null;
        this.registerer = null;
        this.session = null;
        this.callStartTime = null;
        this.callTimer = null;
        this.broadcastChannel = null;
        this.audioElement = null;
        this.localStream = null;
        this.pipController = null;
        
        this.initializeBroadcastChannel();
        this.initializeAudio();
        this.initializePip();
    }

    initializePip() {
        try {
            this.pipController = new PipController();
        } catch (error) {
            console.error('Failed to initialize PiP controller:', error);
        }
    }

    initializeBroadcastChannel() {
        if (!('BroadcastChannel' in window)) {
            console.warn('BroadcastChannel not supported');
            return;
        }

        this.broadcastChannel = new BroadcastChannel('crm-agent-phone');
        
        this.broadcastChannel.addEventListener('message', (event) => {
            const message = event.data;
            
            if (message.type === 'CALL_REQUEST') {
                this.handleClickToCall(message);
            }
        });
    }

    initializeAudio() {
        this.audioElement = document.createElement('audio');
        this.audioElement.autoplay = true;
        document.body.appendChild(this.audioElement);
    }

    async register() {
        try {
            const uri = `sip:${this.config.sip_username}@${this.config.sip_domain}`;
            const transportOptions = {
                server: this.config.sip_ws_url,
            };

            this.userAgent = new UserAgent({
                uri: UserAgent.makeURI(uri),
                authorizationUsername: this.config.sip_username,
                authorizationPassword: this.config.sip_password,
                transportOptions,
                displayName: this.config.display_name,
                delegate: {
                    onInvite: (invitation) => {
                        this.handleIncomingCall(invitation);
                    }
                }
            });

            await this.userAgent.start();

            this.registerer = new Registerer(this.userAgent);
            
            this.registerer.stateChange.addListener((state) => {
                console.log('Registerer state:', state);
                
                if (state === 'Registered') {
                    this.livewire.dispatch('sipRegistered');
                    this.postBroadcastMessage('REGISTERED', {});
                } else if (state === 'Unregistered') {
                    this.livewire.dispatch('sipUnregistered');
                    this.postBroadcastMessage('UNREGISTERED', {});
                }
            });

            await this.registerer.register();
            
        } catch (error) {
            console.error('Registration failed:', error);
            this.livewire.dispatch('sipRegistrationFailed', { 
                message: error.message || 'Unknown error' 
            });
        }
    }

    async unregister() {
        try {
            if (this.session) {
                await this.hangup();
            }

            if (this.registerer) {
                await this.registerer.unregister();
                await this.registerer.dispose();
                this.registerer = null;
            }

            if (this.userAgent) {
                await this.userAgent.stop();
                this.userAgent = null;
            }
        } catch (error) {
            console.error('Unregistration failed:', error);
        }
    }

    async makeCall(number) {
        if (!this.userAgent) {
            console.error('UserAgent not initialized');
            return;
        }

        if (this.session) {
            console.error('Call already in progress');
            return;
        }

        try {
            const target = UserAgent.makeURI(`sip:${number}@${this.config.sip_domain}`);
            
            if (!target) {
                throw new Error('Invalid call target');
            }

            this.session = new Inviter(this.userAgent, target, {
                sessionDescriptionHandlerOptions: {
                    constraints: {
                        audio: true,
                        video: false
                    }
                }
            });

            this.setupSessionHandlers(this.session);

            await this.session.invite();

            this.callStartTime = Date.now();
            this.startCallTimer();

            this.livewire.dispatch('callStarted', {
                direction: 'outbound',
                from_number: this.config.sip_username,
                to_number: number,
                pbx_call_id: this.session.id
            });

            this.postBroadcastMessage('CALL_STARTED', {
                number: number,
                direction: 'outbound'
            });

        } catch (error) {
            console.error('Make call failed:', error);
            this.livewire.dispatch('callFailed', { 
                message: error.message || 'Failed to make call' 
            });
            this.postBroadcastMessage('CALL_FAILED', {
                reason: error.message
            });
        }
    }

    async answerCall() {
        if (!this.session) {
            console.error('No incoming call to answer');
            return;
        }

        try {
            const options = {
                sessionDescriptionHandlerOptions: {
                    constraints: {
                        audio: true,
                        video: false
                    }
                }
            };

            await this.session.accept(options);
            
            this.callStartTime = Date.now();
            this.startCallTimer();

            this.livewire.dispatch('callAnswered');
            
            this.postBroadcastMessage('CALL_ANSWERED', {
                number: this.session.remoteIdentity.uri.user
            });

        } catch (error) {
            console.error('Answer call failed:', error);
            this.livewire.dispatch('callFailed', { 
                message: error.message || 'Failed to answer call' 
            });
        }
    }

    async hangup() {
        if (!this.session) {
            return;
        }

        try {
            const durationSeconds = this.callStartTime 
                ? Math.floor((Date.now() - this.callStartTime) / 1000) 
                : 0;

            switch (this.session.state) {
                case SessionState.Initial:
                case SessionState.Establishing:
                    if (this.session instanceof Inviter) {
                        await this.session.cancel();
                    } else {
                        await this.session.reject();
                    }
                    break;
                case SessionState.Established:
                    await this.session.bye();
                    break;
            }

            this.stopCallTimer();

            this.livewire.dispatch('callEnded', {
                duration_seconds: durationSeconds
            });

            this.postBroadcastMessage('CALL_ENDED', {
                duration_seconds: durationSeconds
            });

            this.cleanupSession();

        } catch (error) {
            console.error('Hangup failed:', error);
            this.cleanupSession();
        }
    }

    async toggleMute(muted) {
        if (!this.session) {
            return;
        }

        try {
            const pc = this.session.sessionDescriptionHandler.peerConnection;
            
            if (pc) {
                const senders = pc.getSenders();
                senders.forEach(sender => {
                    if (sender.track && sender.track.kind === 'audio') {
                        sender.track.enabled = !muted;
                    }
                });
            }

            // Update PiP controller
            if (this.pipController) {
                this.pipController.setMuted(muted);
            }
        } catch (error) {
            console.error('Toggle mute failed:', error);
        }
    }

    async toggleHold(held) {
        if (!this.session) {
            return;
        }

        try {
            if (held) {
                await this.session.hold();
            } else {
                await this.session.unhold();
            }
        } catch (error) {
            console.error('Toggle hold failed:', error);
        }
    }

    sendDtmf(digit) {
        if (!this.session || this.session.state !== SessionState.Established) {
            return;
        }

        try {
            const options = {
                requestOptions: {
                    body: {
                        contentDisposition: 'render',
                        contentType: 'application/dtmf-relay',
                        content: `Signal=${digit}\r\nDuration=100`
                    }
                }
            };

            this.session.info(options);
        } catch (error) {
            console.error('Send DTMF failed:', error);
        }
    }

    handleIncomingCall(invitation) {
        if (this.session) {
            invitation.reject();
            return;
        }

        this.session = invitation;
        this.setupSessionHandlers(invitation);

        const fromNumber = invitation.remoteIdentity.uri.user;
        const toNumber = this.config.sip_username;

        this.livewire.dispatch('callStarted', {
            direction: 'inbound',
            from_number: fromNumber,
            to_number: toNumber,
            pbx_call_id: invitation.id
        });

        this.postBroadcastMessage('CALL_STARTED', {
            number: fromNumber,
            direction: 'inbound'
        });
    }

    setupSessionHandlers(session) {
        session.stateChange.addListener((state) => {
            console.log('Session state:', state);

            switch (state) {
                case SessionState.Established:
                    this.setupRemoteMedia(session);
                    break;
                case SessionState.Terminated:
                    this.handleSessionTerminated();
                    break;
            }
        });
    }

    setupRemoteMedia(session) {
        const pc = session.sessionDescriptionHandler.peerConnection;
        
        if (!pc) {
            console.error('No peer connection');
            return;
        }

        const remoteStream = new MediaStream();
        
        pc.getReceivers().forEach(receiver => {
            if (receiver.track) {
                remoteStream.addTrack(receiver.track);
            }
        });

        if (this.audioElement) {
            this.audioElement.srcObject = remoteStream;
            this.audioElement.play().catch(error => {
                console.error('Audio play failed:', error);
            });
        }

        // Start Picture-in-Picture with call info
        this.startPictureInPicture();
    }

    async startPictureInPicture() {
        if (!this.pipController || !this.session) {
            return;
        }

        try {
            // Determine phone number based on direction
            let phoneNumber;
            const remoteUser = this.session.remoteIdentity?.uri?.user || 'Unknown';
            
            // Get direction from the session
            const isInbound = !(this.session instanceof Inviter);
            const direction = isInbound ? 'inbound' : 'outbound';
            
            phoneNumber = remoteUser;

            const callData = {
                phoneNumber,
                direction
            };

            const actionHandlers = {
                hangup: () => {
                    console.log('Hangup from Media Session');
                    this.hangup();
                },
                mute: () => {
                    console.log('Mute from Media Session');
                    this.livewire.dispatch('toggleMute', { muted: true });
                },
                unmute: () => {
                    console.log('Unmute from Media Session');
                    this.livewire.dispatch('toggleMute', { muted: false });
                }
            };

            await this.pipController.startPip(callData, actionHandlers);
        } catch (error) {
            console.error('Failed to start Picture-in-Picture:', error);
        }
    }

    handleSessionTerminated() {
        const durationSeconds = this.callStartTime 
            ? Math.floor((Date.now() - this.callStartTime) / 1000) 
            : 0;

        this.stopCallTimer();

        this.livewire.dispatch('callEnded', {
            duration_seconds: durationSeconds
        });

        this.postBroadcastMessage('CALL_ENDED', {
            duration_seconds: durationSeconds
        });

        this.cleanupSession();
    }

    cleanupSession() {
        if (this.audioElement) {
            this.audioElement.srcObject = null;
        }

        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }

        // Stop Picture-in-Picture
        if (this.pipController) {
            this.pipController.stopPip();
        }

        this.session = null;
        this.callStartTime = null;
    }

    startCallTimer() {
        this.stopCallTimer();
        
        this.callTimer = setInterval(() => {
            if (this.callStartTime) {
                const seconds = Math.floor((Date.now() - this.callStartTime) / 1000);
                this.livewire.dispatch('callTimerUpdate', seconds);
            }
        }, 1000);
    }

    stopCallTimer() {
        if (this.callTimer) {
            clearInterval(this.callTimer);
            this.callTimer = null;
        }
    }

    handleClickToCall(message) {
        if (!message.number) {
            console.error('No number in click-to-call message');
            return;
        }

        console.log('Click-to-call request:', message);
        this.makeCall(message.number);
    }

    postBroadcastMessage(type, data) {
        if (!this.broadcastChannel) {
            return;
        }

        try {
            this.broadcastChannel.postMessage({
                type,
                timestamp: Date.now(),
                ...data
            });
        } catch (error) {
            console.error('Failed to post broadcast message:', error);
        }
    }

    cleanup() {
        this.stopCallTimer();
        
        if (this.session) {
            this.hangup();
        }

        if (this.registerer) {
            this.unregister();
        }

        if (this.broadcastChannel) {
            this.broadcastChannel.close();
            this.broadcastChannel = null;
        }

        if (this.audioElement) {
            this.audioElement.remove();
            this.audioElement = null;
        }

        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }

        if (this.pipController) {
            this.pipController.destroy();
            this.pipController = null;
        }
    }
}

// Setup Livewire event listeners
if (typeof Livewire !== 'undefined') {
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('registerSip', () => {
            if (window.sipAgent) {
                window.sipAgent.register();
            }
        });

        Livewire.on('unregisterSip', () => {
            if (window.sipAgent) {
                window.sipAgent.unregister();
            }
        });

        Livewire.on('makeOutboundCall', (data) => {
            if (window.sipAgent) {
                window.sipAgent.makeCall(data.number);
            }
        });

        Livewire.on('answerInboundCall', () => {
            if (window.sipAgent) {
                window.sipAgent.answerCall();
            }
        });

        Livewire.on('hangupCall', () => {
            if (window.sipAgent) {
                window.sipAgent.hangup();
            }
        });

        Livewire.on('toggleMute', (data) => {
            if (window.sipAgent) {
                window.sipAgent.toggleMute(data.muted);
            }
        });

        Livewire.on('toggleHold', (data) => {
            if (window.sipAgent) {
                window.sipAgent.toggleHold(data.held);
            }
        });

        Livewire.on('sendDtmf', (data) => {
            if (window.sipAgent) {
                window.sipAgent.sendDtmf(data.digit);
            }
        });
    });
}
