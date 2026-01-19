<?php

namespace App\Pipelines\LeadPipeline;

use App\Models\Lead;
use Closure;

/**
 * Notify Assigned User Pipeline Stage
 *
 * Placeholder for notification logic in the pipeline.
 * Actual notifications are handled via LeadAssigned event and listener.
 * This stage can be used for synchronous validation or other pre-assignment checks.
 */
class NotifyAssignedUser
{
    /**
     * Handle the pipeline stage
     *
     * @param Lead|array<string, mixed> $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(Lead|array $data, Closure $next): mixed
    {
        // This stage is optional and can be removed if not needed
        // The actual notification is handled by the LeadAssigned event
        // which is dispatched in LeadService::assignLead()
        
        return $next($data);
    }
}
