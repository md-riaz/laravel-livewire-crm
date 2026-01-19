<?php

namespace App\Services;

use App\Contracts\ActivityLoggerInterface;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Activity Logger Service
 *
 * Centralized activity logging for the CRM system.
 * Handles both lead-specific activities and general audit logs.
 */
readonly class ActivityLoggerService implements ActivityLoggerInterface
{
    /**
     * Log an action performed on an entity
     */
    public function log(string $action, Model $entity, ?array $metadata = null): void
    {
        // Create audit log entry
        AuditLog::create([
            'tenant_id' => $entity->tenant_id ?? null,
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($entity),
            'auditable_id' => $entity->id,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log an activity specific to a lead
     */
    public function logForLead(Lead $lead, string $type, array $payload): void
    {
        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => $type,
            'description' => $this->buildDescription($type, $payload),
            'metadata' => $payload,
        ]);

        // Also log to audit log for comprehensive tracking
        $this->log("lead_{$type}", $lead, $payload);
    }

    /**
     * Build a human-readable description from activity data
     */
    private function buildDescription(string $type, array $payload): string
    {
        return match ($type) {
            'created' => 'Lead was created',
            'updated' => 'Lead information was updated',
            'assigned' => 'Lead was assigned to a new user',
            'status_changed' => 'Lead status was changed',
            'followup_scheduled' => 'Follow-up was scheduled',
            'note_added' => 'Note was added to the lead',
            'email_sent' => 'Email was sent to the lead',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
