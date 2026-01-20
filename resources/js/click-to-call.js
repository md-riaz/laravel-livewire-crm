/**
 * Click-to-Call Helper
 * 
 * This module provides utilities for initiating calls from anywhere in the application
 * using the BroadcastChannel API to communicate with the Agent Console.
 * 
 * Usage example:
 * 
 * HTML:
 * <button onclick="clickToCall('+15551234567', 'lead', 123)">
 *   Call Lead
 * </button>
 * 
 * Or with Alpine.js:
 * <button @click="$dispatch('click-to-call', { number: '+15551234567', relatedType: 'lead', relatedId: 123 })">
 *   Call Lead
 * </button>
 */

class ClickToCallManager {
    constructor() {
        this.channel = null;
        this.initialize();
    }

    initialize() {
        if (!('BroadcastChannel' in window)) {
            console.warn('BroadcastChannel API not supported');
            return;
        }

        try {
            this.channel = new BroadcastChannel('crm-agent-phone');
            
            // Listen for responses from Agent Console
            this.channel.addEventListener('message', (event) => {
                this.handleResponse(event.data);
            });
        } catch (error) {
            console.error('Failed to initialize click-to-call:', error);
        }
    }

    /**
     * Initiate a call to a phone number
     * 
     * @param {string} number - Phone number to call (with country code)
     * @param {string} relatedType - Related entity type ('lead', 'contact', etc.)
     * @param {number} relatedId - Related entity ID
     * @param {string} requestId - Optional unique request ID
     * @returns {boolean} - True if request was sent, false otherwise
     */
    makeCall(number, relatedType = null, relatedId = null, requestId = null) {
        if (!this.channel) {
            this.showNotification('Click-to-Call is not available', 'error');
            return false;
        }

        if (!number) {
            this.showNotification('Phone number is required', 'error');
            return false;
        }

        try {
            const message = {
                type: 'CALL_REQUEST',
                number: number,
                relatedType: relatedType,
                relatedId: relatedId,
                requestId: requestId || this.generateRequestId(),
                timestamp: Date.now()
            };

            this.channel.postMessage(message);
            
            this.showNotification('Call request sent to Agent Console', 'info');
            
            return true;
        } catch (error) {
            console.error('Failed to send call request:', error);
            this.showNotification('Failed to initiate call', 'error');
            return false;
        }
    }

    /**
     * Handle responses from Agent Console
     */
    handleResponse(message) {
        switch (message.type) {
            case 'CALL_STARTED':
                this.showNotification(`Call started to ${message.number}`, 'success');
                break;
            case 'CALL_ANSWERED':
                this.showNotification('Call answered', 'success');
                break;
            case 'CALL_ENDED':
                const duration = message.duration_seconds 
                    ? ` (${Math.floor(message.duration_seconds / 60)}:${String(message.duration_seconds % 60).padStart(2, '0')})` 
                    : '';
                this.showNotification(`Call ended${duration}`, 'info');
                break;
            case 'CALL_FAILED':
                this.showNotification(`Call failed: ${message.reason || 'Unknown error'}`, 'error');
                break;
        }
    }

    /**
     * Generate a unique request ID
     */
    generateRequestId() {
        return `call-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;
    }

    /**
     * Show notification to user
     * This is a basic implementation - you should customize this based on your notification system
     */
    showNotification(message, type = 'info') {
        console.log(`[Click-to-Call ${type.toUpperCase()}]:`, message);
        
        // If you have a custom notification system, integrate it here
        // For now, we'll use a simple console log
        // You could also dispatch a Livewire event:
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('notification', { message, type });
        }
    }

    /**
     * Cleanup on page unload
     */
    cleanup() {
        if (this.channel) {
            this.channel.close();
            this.channel = null;
        }
    }
}

// Initialize the click-to-call manager
const clickToCallManager = new ClickToCallManager();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    clickToCallManager.cleanup();
});

// Global function for easy access from HTML
window.clickToCall = (number, relatedType = null, relatedId = null) => {
    return clickToCallManager.makeCall(number, relatedType, relatedId);
};

// Export for ES6 modules
export { clickToCallManager, ClickToCallManager };

// Alpine.js directive for click-to-call
if (typeof Alpine !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        Alpine.directive('click-to-call', (el, { expression }, { evaluate }) => {
            el.addEventListener('click', () => {
                const data = evaluate(expression);
                if (typeof data === 'string') {
                    // Simple phone number
                    clickToCallManager.makeCall(data);
                } else if (typeof data === 'object') {
                    // Full options object
                    clickToCallManager.makeCall(
                        data.number,
                        data.relatedType || null,
                        data.relatedId || null,
                        data.requestId || null
                    );
                }
            });
        });
    });
}

// Livewire integration
if (typeof Livewire !== 'undefined') {
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('click-to-call', (data) => {
            clickToCallManager.makeCall(
                data.number,
                data.relatedType || null,
                data.relatedId || null,
                data.requestId || null
            );
        });
    });
}
