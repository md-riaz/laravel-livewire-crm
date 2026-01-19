<?php

namespace App\Services;

use App\Contracts\CallServiceInterface;
use App\Contracts\CallRepositoryInterface;
use App\Contracts\ActivityLoggerInterface;
use App\Events\CallStarted;
use App\Events\CallEnded;
use App\Events\CallWrappedUp;
use App\Models\Call;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Call Service
 *
 * Handles call lifecycle management with business logic.
 * Manages call states, dispositions, and follow-up scheduling.
 */
readonly class CallService implements CallServiceInterface
{
    public function __construct(
        private CallRepositoryInterface $callRepository,
        private ActivityLoggerInterface $activityLogger
    ) {}

    /**
     * Start a new call
     * 
     * Creates a call record and optionally links it to a lead.
     */
    public function startCall(array $data): Call
    {
        return DB::transaction(function () use ($data) {
            // Set started timestamp
            $data['started_at'] = now();

            // Auto-link to lead if configured and lead info is present
            if (config('crm.calls.auto_link_to_lead', true) && isset($data['lead_id'])) {
                $data['related_type'] = Lead::class;
                $data['related_id'] = $data['lead_id'];
                unset($data['lead_id']);
            }

            $call = $this->callRepository->create($data);

            // Log activity
            $this->activityLogger->log('call_started', $call, [
                'direction' => $call->direction,
                'from_number' => $call->from_number,
                'to_number' => $call->to_number,
            ]);

            // Dispatch event
            event(new CallStarted($call));

            return $call;
        });
    }

    /**
     * End an active call
     */
    public function endCall(Call $call): Call
    {
        return DB::transaction(function () use ($call) {
            $endedAt = now();
            $durationSeconds = $call->started_at->diffInSeconds($endedAt);

            $updated = $this->callRepository->update($call, [
                'ended_at' => $endedAt,
                'duration_seconds' => $durationSeconds,
            ]);

            // Log activity
            $this->activityLogger->log('call_ended', $updated, [
                'duration_seconds' => $durationSeconds,
            ]);

            // Dispatch event
            event(new CallEnded($updated));

            return $updated;
        });
    }

    /**
     * Wrap up a call with disposition and notes
     */
    public function wrapUpCall(Call $call, int $dispositionId, ?string $notes = null): Call
    {
        return DB::transaction(function () use ($call, $dispositionId, $notes) {
            // Validate mandatory wrap-up if configured
            if (config('crm.calls.mandatory_wrapup', true)) {
                $disposition = \App\Models\CallDisposition::find($dispositionId);
                if (empty($notes) && $disposition?->requires_note) {
                    throw new \InvalidArgumentException('Notes are required for this disposition');
                }
            }

            $updated = $this->callRepository->update($call, [
                'disposition_id' => $dispositionId,
                'wrapup_notes' => $notes,
            ]);

            // Log activity
            $this->activityLogger->log('call_wrapped_up', $updated, [
                'disposition_id' => $dispositionId,
                'has_notes' => !empty($notes),
            ]);

            // Dispatch event (UpdateLeadTimestamps listener handles lead timestamp update)
            event(new CallWrappedUp($updated));

            return $updated;
        });
    }

    /**
     * Schedule a follow-up for a call
     */
    public function scheduleFollowUp(Call $call, Carbon $date): void
    {
        // If call is linked to a lead, update the lead's follow-up date
        if ($call->related_type === Lead::class && $call->related_id) {
            $lead = Lead::find($call->related_id);
            if ($lead) {
                $lead->update(['next_followup_at' => $date]);
                
                $this->activityLogger->logForLead(
                    $lead,
                    'followup_scheduled',
                    [
                        'scheduled_for' => $date->toDateTimeString(),
                        'call_id' => $call->id,
                    ]
                );
            }
        }
    }
}
