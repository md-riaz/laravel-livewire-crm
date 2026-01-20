<?php

namespace App\Http\Controllers\Api;

use App\Actions\WrapUpCallAction;
use App\DTOs\Responses\CallResource;
use App\DTOs\Responses\CallCollection;
use App\Http\Requests\WrapUpCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;

/**
 * Call Controller using DTOs and Actions
 *
 * Demonstrates the proper usage of the DTO layer for production use.
 */
class CallControllerWithDTOs extends Controller
{
    /**
     * List calls for current user
     */
    public function index(): JsonResponse
    {
        $calls = Call::where('tenant_id', auth()->user()->tenant_id)
            ->where('user_id', auth()->user()->id)
            ->with(['disposition', 'user'])
            ->orderBy('started_at', 'desc')
            ->paginate(15);
        
        return response()->json(
            CallCollection::fromPaginator($calls)->toArray()
        );
    }

    /**
     * Show single call
     */
    public function show(Call $call): JsonResponse
    {
        $this->authorize('view', $call);
        
        return response()->json([
            'data' => CallResource::fromModel($call->load(['disposition', 'user']))->toArray(),
        ]);
    }

    /**
     * Wrap up a call
     */
    public function wrapUp(Call $call, WrapUpCallRequest $request, WrapUpCallAction $action): JsonResponse
    {
        $dto = $request->toDTO();
        $updatedCall = $action->execute($dto);
        
        return response()->json([
            'message' => 'Call wrapped up successfully',
            'data' => CallResource::fromModel($updatedCall->load(['disposition', 'user']))->toArray(),
        ]);
    }

    /**
     * Get call statistics
     */
    public function statistics(): JsonResponse
    {
        $calls = Call::where('tenant_id', auth()->user()->tenant_id)
            ->where('user_id', auth()->user()->id)
            ->whereNotNull('ended_at')
            ->get();
        
        $collection = CallCollection::fromModels($calls);
        
        return response()->json([
            'total_calls' => $collection->count(),
            'total_duration' => $collection->getTotalDuration(),
            'average_duration' => $collection->getAverageDuration(),
        ]);
    }
}
