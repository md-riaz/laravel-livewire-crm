<?php

namespace App\Http\Controllers\Api;

use App\Contracts\LeadServiceInterface;
use App\Contracts\LeadRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Lead API Controller
 *
 * Example implementation showing how to use the enterprise services
 * and repositories in a RESTful API context.
 */
class LeadController extends Controller
{
    public function __construct(
        private readonly LeadServiceInterface $leadService,
        private readonly LeadRepositoryInterface $leadRepository
    ) {}

    /**
     * List leads with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $leads = $this->leadRepository->search([
            'status_id' => $request->input('status_id'),
            'assigned_to' => $request->input('assigned_to'),
            'score' => $request->input('score'),
            'search' => $request->input('q'),
            'order_by' => $request->input('order_by', 'created_at'),
            'order_direction' => $request->input('order_direction', 'desc'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $leads,
            'count' => $leads->count(),
        ]);
    }

    /**
     * Get a specific lead with relationships
     */
    public function show(int $id): JsonResponse
    {
        $lead = $this->leadRepository->findWithRelations($id, [
            'assignedTo',
            'createdBy',
            'status',
            'activities',
            'calls',
        ]);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    /**
     * Create a new lead
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $lead = $this->leadService->createLead([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'company_name' => $request->input('company_name'),
                'source' => $request->input('source'),
                'score' => $request->input('score'),
                'estimated_value' => $request->input('estimated_value'),
                'assigned_to_user_id' => $request->input('assigned_to_user_id'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $lead,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update an existing lead
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $lead = $this->leadRepository->find($id);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $updated = $this->leadService->updateLead($lead, $request->only([
            'name',
            'email',
            'phone',
            'company_name',
            'score',
            'estimated_value',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $updated,
        ]);
    }

    /**
     * Assign a lead to a user
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $lead = $this->leadRepository->find($id);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $assigned = $this->leadService->assignLead($lead, $request->input('user_id'));

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned successfully',
            'data' => $assigned,
        ]);
    }

    /**
     * Move a lead to a different status
     */
    public function moveStatus(Request $request, int $id): JsonResponse
    {
        $lead = $this->leadRepository->find($id);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $request->validate([
            'status_id' => 'required|integer|exists:lead_statuses,id',
            'note' => 'nullable|string|max:500',
        ]);

        $moved = $this->leadService->moveLead(
            $lead,
            $request->input('status_id'),
            $request->input('note')
        );

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully',
            'data' => $moved,
        ]);
    }

    /**
     * Get leads requiring follow-up
     */
    public function requiresFollowUp(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', auth()->id());
        $leads = $this->leadRepository->getRequiringFollowUp($userId);

        return response()->json([
            'success' => true,
            'data' => $leads,
            'count' => $leads->count(),
        ]);
    }
}
