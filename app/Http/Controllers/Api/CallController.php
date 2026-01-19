<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CallServiceInterface;
use App\Contracts\CallRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Call API Controller
 *
 * Example implementation showing how to use the call services
 * for managing call lifecycle through a RESTful API.
 */
class CallController extends Controller
{
    public function __construct(
        private readonly CallServiceInterface $callService,
        private readonly CallRepositoryInterface $callRepository
    ) {}

    /**
     * Start a new call
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'direction' => 'required|in:inbound,outbound',
            'from_number' => 'required|string',
            'to_number' => 'required|string',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'pbx_call_id' => 'nullable|string',
        ]);

        $call = $this->callService->startCall([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'direction' => $request->input('direction'),
            'from_number' => $request->input('from_number'),
            'to_number' => $request->input('to_number'),
            'lead_id' => $request->input('lead_id'),
            'pbx_call_id' => $request->input('pbx_call_id'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call started successfully',
            'data' => $call,
        ], 201);
    }

    /**
     * End an active call
     */
    public function end(int $id): JsonResponse
    {
        $call = $this->callRepository->find($id);

        if (!$call) {
            return response()->json([
                'success' => false,
                'message' => 'Call not found',
            ], 404);
        }

        if ($call->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'Call already ended',
            ], 422);
        }

        $ended = $this->callService->endCall($call);

        return response()->json([
            'success' => true,
            'message' => 'Call ended successfully',
            'data' => $ended,
        ]);
    }

    /**
     * Wrap up a call with disposition
     */
    public function wrapUp(Request $request, int $id): JsonResponse
    {
        $call = $this->callRepository->find($id);

        if (!$call) {
            return response()->json([
                'success' => false,
                'message' => 'Call not found',
            ], 404);
        }

        $request->validate([
            'disposition_id' => 'required|integer|exists:call_dispositions,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $wrapped = $this->callService->wrapUpCall(
                $call,
                $request->input('disposition_id'),
                $request->input('notes')
            );

            return response()->json([
                'success' => true,
                'message' => 'Call wrapped up successfully',
                'data' => $wrapped,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Schedule a follow-up for a call
     */
    public function scheduleFollowUp(Request $request, int $id): JsonResponse
    {
        $call = $this->callRepository->find($id);

        if (!$call) {
            return response()->json([
                'success' => false,
                'message' => 'Call not found',
            ], 404);
        }

        $request->validate([
            'followup_date' => 'required|date|after:now',
        ]);

        $this->callService->scheduleFollowUp(
            $call,
            Carbon::parse($request->input('followup_date'))
        );

        return response()->json([
            'success' => true,
            'message' => 'Follow-up scheduled successfully',
        ]);
    }

    /**
     * Get active calls for the authenticated user
     */
    public function activeCalls(): JsonResponse
    {
        $calls = $this->callRepository->getActiveCalls(auth()->id());

        return response()->json([
            'success' => true,
            'data' => $calls,
            'count' => $calls->count(),
        ]);
    }

    /**
     * Get calls requiring wrap-up
     */
    public function requiresWrapUp(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', auth()->id());
        $calls = $this->callRepository->getRequiringWrapUp($userId);

        return response()->json([
            'success' => true,
            'data' => $calls,
            'count' => $calls->count(),
        ]);
    }

    /**
     * Get call history for a specific entity (lead)
     */
    public function entityHistory(Request $request): JsonResponse
    {
        $request->validate([
            'related_type' => 'required|string',
            'related_id' => 'required|integer',
        ]);

        $calls = $this->callRepository->getForEntity(
            $request->input('related_type'),
            $request->input('related_id')
        );

        return response()->json([
            'success' => true,
            'data' => $calls,
            'count' => $calls->count(),
        ]);
    }
}
