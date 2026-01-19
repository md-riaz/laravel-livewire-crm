<?php

namespace App\Actions;

use App\Contracts\CallServiceInterface;
use App\DTOs\CallWrapUpDTO;
use App\Models\Call;
use Illuminate\Support\Facades\DB;

/**
 * Wrap Up Call Action
 *
 * Single-purpose action for completing call wrap-up.
 * Handles disposition, notes, and follow-up actions.
 */
readonly class WrapUpCallAction
{
    public function __construct(
        private CallServiceInterface $callService
    ) {}

    /**
     * Execute the action to wrap up a call
     *
     * @param CallWrapUpDTO $dto Wrap-up data transfer object
     * @return Call Updated call model
     */
    public function execute(CallWrapUpDTO $dto): Call
    {
        return DB::transaction(function () use ($dto) {
            $call = Call::findOrFail($dto->call_id);

            // Update call with wrap-up data - explicit null checks
            $updateData = [];
            
            if ($dto->disposition_id !== null) {
                $updateData['disposition_id'] = $dto->disposition_id;
            }
            
            if ($dto->wrapup_notes !== null) {
                $updateData['wrapup_notes'] = $dto->wrapup_notes;
            }
            
            if ($dto->related_id !== null) {
                $updateData['related_id'] = $dto->related_id;
            }
            
            if ($dto->related_type !== null) {
                $updateData['related_type'] = $dto->related_type;
            }

            $call->update($updateData);

            // Handle follow-up actions if provided
            if ($dto->requiresFollowUp()) {
                $this->handleFollowUpActions($call, $dto->follow_up_actions);
            }

            return $call->fresh();
        });
    }

    /**
     * Handle follow-up actions for the call
     *
     * @param Call $call
     * @param array<string>|null $actions
     */
    private function handleFollowUpActions(Call $call, ?array $actions): void
    {
        if (empty($actions)) {
            return;
        }

        // Log follow-up actions or create tasks
        // This can be extended based on business requirements
        foreach ($actions as $action) {
            // Implementation depends on your follow-up system
            // Could create tasks, set reminders, etc.
        }
    }
}
