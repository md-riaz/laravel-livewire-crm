<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Notify Assigned User Listener
 *
 * Sends notification when a lead is assigned to a user.
 * Implements ShouldQueue for async processing.
 */
class NotifyAssignedUser implements ShouldQueue
{
    /**
     * Handle the lead assigned event
     */
    public function handle(LeadAssigned $event): void
    {
        // Skip if notifications are disabled
        if (!config('crm.pipeline.notify_on_assignment', true)) {
            return;
        }

        $user = User::find($event->newUserId);
        
        if ($user) {
            // Notification implementation would go here
            // Example: $user->notify(new LeadAssignedNotification($event->lead));
            // For production, create a notification class:
            // php artisan make:notification LeadAssignedNotification
        }
    }
}
