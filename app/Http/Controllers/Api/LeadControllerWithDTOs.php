<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateLeadAction;
use App\Actions\UpdateLeadAction;
use App\Actions\AssignLeadAction;
use App\Actions\MoveLeadAction;
use App\DTOs\Responses\LeadResource;
use App\DTOs\Responses\LeadCollection;
use App\Http\Requests\CreateLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\SearchLeadsRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;

/**
 * Lead Controller using DTOs and Actions
 *
 * Demonstrates the proper usage of the DTO layer for production use.
 */
class LeadControllerWithDTOs extends Controller
{
    /**
     * List leads with search filters
     */
    public function index(SearchLeadsRequest $request): JsonResponse
    {
        $searchDTO = $request->toDTO();
        
        $query = Lead::query()
            ->where('tenant_id', $searchDTO->tenant_id)
            ->with(['status', 'assignedTo']);
        
        // Apply filters
        if ($searchDTO->search) {
            $query->where(function ($q) use ($searchDTO) {
                $q->where('name', 'like', "%{$searchDTO->search}%")
                  ->orWhere('email', 'like', "%{$searchDTO->search}%")
                  ->orWhere('company_name', 'like', "%{$searchDTO->search}%");
            });
        }
        
        if ($searchDTO->lead_status_id) {
            $query->where('lead_status_id', $searchDTO->lead_status_id);
        }
        
        if ($searchDTO->assigned_to_user_id) {
            $query->where('assigned_to_user_id', $searchDTO->assigned_to_user_id);
        }
        
        if ($searchDTO->score) {
            $query->where('score', $searchDTO->score);
        }
        
        if ($searchDTO->source) {
            $query->where('source', $searchDTO->source);
        }
        
        if ($searchDTO->min_estimated_value) {
            $query->where('estimated_value', '>=', $searchDTO->min_estimated_value);
        }
        
        if ($searchDTO->max_estimated_value) {
            $query->where('estimated_value', '<=', $searchDTO->max_estimated_value);
        }
        
        // Sort and paginate
        $leads = $query
            ->orderBy($searchDTO->sort_by, $searchDTO->sort_direction)
            ->paginate($searchDTO->per_page);
        
        return response()->json(
            LeadCollection::fromPaginator($leads)->toArray()
        );
    }

    /**
     * Show single lead
     */
    public function show(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        
        return response()->json([
            'data' => LeadResource::fromModel($lead->load(['status', 'assignedTo']))->toArray(),
        ]);
    }

    /**
     * Create new lead
     */
    public function store(CreateLeadRequest $request, CreateLeadAction $action): JsonResponse
    {
        $dto = $request->toDTO();
        $lead = $action->execute($dto);
        
        return response()->json([
            'message' => 'Lead created successfully',
            'data' => LeadResource::fromModel($lead->load(['status', 'assignedTo']))->toArray(),
        ], 201);
    }

    /**
     * Update existing lead
     */
    public function update(Lead $lead, UpdateLeadRequest $request, UpdateLeadAction $action): JsonResponse
    {
        $dto = $request->toDTO();
        $updatedLead = $action->execute($lead, $dto);
        
        return response()->json([
            'message' => 'Lead updated successfully',
            'data' => LeadResource::fromModel($updatedLead->load(['status', 'assignedTo']))->toArray(),
        ]);
    }

    /**
     * Assign lead to user
     */
    public function assign(Lead $lead, AssignLeadAction $action): JsonResponse
    {
        $this->authorize('assign', $lead);
        
        $userId = request()->input('user_id');
        $updatedLead = $action->execute($lead, $userId);
        
        return response()->json([
            'message' => 'Lead assigned successfully',
            'data' => LeadResource::fromModel($updatedLead->load(['status', 'assignedTo']))->toArray(),
        ]);
    }

    /**
     * Move lead to different status
     */
    public function move(Lead $lead, MoveLeadAction $action): JsonResponse
    {
        $this->authorize('move', $lead);
        
        $statusId = request()->input('status_id');
        $note = request()->input('note');
        
        $updatedLead = $action->execute($lead, $statusId, $note);
        
        return response()->json([
            'message' => 'Lead moved successfully',
            'data' => LeadResource::fromModel($updatedLead->load(['status', 'assignedTo']))->toArray(),
        ]);
    }

    /**
     * Delete lead
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);
        
        $lead->delete();
        
        return response()->json([
            'message' => 'Lead deleted successfully',
        ]);
    }
}
