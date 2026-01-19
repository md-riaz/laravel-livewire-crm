<?php

namespace App\Contracts;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;

/**
 * Activity Logger Interface
 *
 * Defines the contract for logging activities and actions
 * performed on entities within the CRM system.
 */
interface ActivityLoggerInterface
{
    /**
     * Log an action performed on an entity
     *
     * @param string $action Action name/type
     * @param Model $entity Entity the action was performed on
     * @param array<string, mixed>|null $metadata Additional metadata about the action
     * @return void
     */
    public function log(string $action, Model $entity, ?array $metadata = null): void;

    /**
     * Log an activity specific to a lead
     *
     * @param Lead $lead Lead to log activity for
     * @param string $type Activity type
     * @param array<string, mixed> $payload Activity data
     * @return void
     */
    public function logForLead(Lead $lead, string $type, array $payload): void;
}
