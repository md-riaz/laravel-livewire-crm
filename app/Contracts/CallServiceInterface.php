<?php

namespace App\Contracts;

use App\Models\Call;
use Carbon\Carbon;

/**
 * Call Service Interface
 *
 * Defines the contract for call management operations including
 * call lifecycle management, dispositions, and follow-up scheduling.
 */
interface CallServiceInterface
{
    /**
     * Start a new call
     *
     * @param array<string, mixed> $data Call data including direction, numbers, etc.
     * @return Call
     */
    public function startCall(array $data): Call;

    /**
     * End an active call
     *
     * @param Call $call Call to end
     * @return Call
     */
    public function endCall(Call $call): Call;

    /**
     * Wrap up a call with disposition and notes
     *
     * @param Call $call Call to wrap up
     * @param int $dispositionId Disposition ID
     * @param string|null $notes Optional wrap-up notes
     * @return Call
     */
    public function wrapUpCall(Call $call, int $dispositionId, ?string $notes = null): Call;

    /**
     * Schedule a follow-up for a call
     *
     * @param Call $call Call to schedule follow-up for
     * @param Carbon $date Follow-up date
     * @return void
     */
    public function scheduleFollowUp(Call $call, Carbon $date): void;
}
