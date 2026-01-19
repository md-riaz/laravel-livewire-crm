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
            // TODO: Implement notification when notification system is ready
            // Example implementation:
            // $user->notify(new LeadAssignedNotification($event->lead));
            // 
            // To add notifications:
            // 1. Run: php artisan make:notification LeadAssignedNotification
            // 2. Implement the notification class
            // 3. Uncomment the line above
        }
    }
}
