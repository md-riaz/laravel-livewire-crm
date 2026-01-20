<?php

namespace App\Pipelines\LeadPipeline;

use App\Models\LeadStatus;
use Closure;

/**
 * Assign Default Status Pipeline Stage
 *
 * Assigns the default lead status if not already set.
 * Ensures every lead has a valid status upon creation.
 */
class AssignDefaultStatus
{
    /**
     * Handle the default status assignment
     *
     * @param array<string, mixed> $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(array $data, Closure $next): mixed
    {
        // Skip if status already assigned
        if (!empty($data['lead_status_id'])) {
            return $next($data);
        }

        // Get default status for the tenant
        $tenantId = $data['tenant_id'] ?? auth()->user()?->tenant_id;
        
        if ($tenantId) {
            $defaultStatus = LeadStatus::query()
                ->where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->first();

            if ($defaultStatus) {
                $data['lead_status_id'] = $defaultStatus->id;
            } else {
                // Fallback to first status if no default is set
                $firstStatus = LeadStatus::query()
                    ->where('tenant_id', $tenantId)
                    ->orderBy('sort_order')
                    ->first();

                if ($firstStatus) {
                    $data['lead_status_id'] = $firstStatus->id;
                }
            }
        }

        return $next($data);
    }
}
