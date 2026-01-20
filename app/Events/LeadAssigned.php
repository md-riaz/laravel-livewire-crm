<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Lead Assigned Event
 *
 * Dispatched when a lead is assigned to a user.
 */
class LeadAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly ?int $previousUserId,
        public readonly int $newUserId
    ) {}
}
