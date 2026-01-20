<?php

namespace App\Listeners;

use App\Events\LeadCreated;
use App\Events\LeadStatusChanged;
use App\Contracts\ActivityLoggerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Log Lead Activity Listener
 *
 * Listens to lead events and logs activities.
 * Implements ShouldQueue for async processing.
 */
class LogLeadActivity implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLoggerInterface $activityLogger
    ) {}

    /**
     * Handle lead created event
     * 
     * TODO: Add additional logging beyond what the service does
     * This can include notifications, integrations, etc.
     */
    public function handleLeadCreated(LeadCreated $event): void
    {
        // Placeholder for future enhancements
    }

    /**
     * Handle lead status changed event
     */
    public function handleLeadStatusChanged(LeadStatusChanged $event): void
    {
        // Log significant status transitions
        $lead = $event->lead;
        
        // Check if moved to closed status
        if ($lead->status?->is_closed) {
            $this->activityLogger->logForLead(
                $lead,
                'closed',
                [
                    'is_won' => $lead->status->is_won ?? false,
                    'is_lost' => $lead->status->is_lost ?? false,
                ]
            );
        }
    }
}
