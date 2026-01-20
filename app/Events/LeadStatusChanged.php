<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Lead Status Changed Event
 *
 * Dispatched when a lead moves to a different status.
 */
class LeadStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly int $previousStatusId,
        public readonly int $newStatusId
    ) {}
}
