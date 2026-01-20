<?php

namespace App\Listeners;

use App\Events\CallWrappedUp;
use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Update Lead Timestamps Listener
 *
 * Updates lead timestamps when calls are completed.
 * Implements ShouldQueue for async processing.
 */
class UpdateLeadTimestamps implements ShouldQueue
{
    /**
     * Handle call wrapped up event
     */
    public function handle(CallWrappedUp $event): void
    {
        $call = $event->call;

        // Update lead's last_contacted_at if the call is linked to a lead
        if ($call->related_type === Lead::class && $call->related_id) {
            $lead = Lead::find($call->related_id);
            if ($lead) {
                $lead->update([
                    'last_contacted_at' => $call->ended_at ?? now(),
                ]);
            }
        }
    }
}
