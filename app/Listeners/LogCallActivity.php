<?php

namespace App\Listeners;

use App\Events\CallStarted;
use App\Events\CallEnded;
use App\Contracts\ActivityLoggerInterface;
use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Log Call Activity Listener
 *
 * Listens to call events and performs side effects.
 * Implements ShouldQueue for async processing.
 */
class LogCallActivity implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLoggerInterface $activityLogger
    ) {}

    /**
     * Handle call started event
     */
    public function handleCallStarted(CallStarted $event): void
    {
        $call = $event->call;

        // If call is linked to a lead, log activity on the lead
        if ($call->related_type === Lead::class && $call->related_id) {
            $lead = Lead::find($call->related_id);
            if ($lead) {
                $this->activityLogger->logForLead(
                    $lead,
                    'call_started',
                    [
                        'call_id' => $call->id,
                        'direction' => $call->direction,
                    ]
                );
            }
        }
    }

    /**
     * Handle call ended event
     */
    public function handleCallEnded(CallEnded $event): void
    {
        $call = $event->call;

        // If call is linked to a lead, log activity on the lead
        if ($call->related_type === Lead::class && $call->related_id) {
            $lead = Lead::find($call->related_id);
            if ($lead) {
                $this->activityLogger->logForLead(
                    $lead,
                    'call_ended',
                    [
                        'call_id' => $call->id,
                        'duration_seconds' => $call->duration_seconds,
                    ]
                );
            }
        }
    }
}
