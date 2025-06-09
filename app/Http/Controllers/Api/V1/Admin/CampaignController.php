<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Contracts\CampaignRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Display a listing of all campaigns for admin.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'category', 'status', 'user_id']);
        $campaigns = $this->campaignRepository->paginate($request->get('per_page', 20), $filters);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Display the specified campaign.
     */
    public function show(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        return response()->json([
            'data' => new CampaignResource($campaign->load(['user', 'donations.user', 'approvedBy']))
        ]);
    }

    /**
     * Enable a campaign (set status to active).
     */
    public function enable(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        // Check if campaign can be enabled
        if (!$this->canBeEnabled($campaign)) {
            return response()->json([
                'message' => 'Campaign cannot be enabled. Please ensure it has proper details and valid dates.'
            ], 422);
        }

        $campaign = $this->campaignRepository->enable($id, auth('api')->id());

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign enabled successfully'
        ]);
    }

    /**
     * Disable a campaign (set status to cancelled).
     */
    public function disable(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        $campaign = $this->campaignRepository->disable($id, $request->input('reason'));

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign disabled successfully'
        ]);
    }

    /**
     * Approve a pending campaign.
     */
    public function approve(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        if ($campaign->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending campaigns can be approved'
            ], 422);
        }

        $campaign = $this->campaignRepository->enable($id, auth('api')->id());

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign approved successfully'
        ]);
    }

    /**
     * Reject a pending campaign.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        if ($campaign->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending campaigns can be rejected'
            ], 422);
        }

        $campaign = $this->campaignRepository->update($id, [
            'status' => 'draft',
            'rejection_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign rejected successfully'
        ]);
    }

    /**
     * Update campaign status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['draft', 'pending', 'active', 'completed', 'cancelled'])],
            'reason' => 'nullable|string|max:500'
        ]);

        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        $updateData = ['status' => $request->status];

        // Set approval data if activating
        if ($request->status === 'active') {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = auth('api')->id();
        }

        // Set rejection reason if provided
        if ($request->has('reason')) {
            $updateData['rejection_reason'] = $request->reason;
        }

        $campaign = $this->campaignRepository->update($id, $updateData);

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign status updated successfully'
        ]);
    }

    /**
     * Toggle featured status of a campaign.
     */
    public function toggleFeatured(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        $campaign = $this->campaignRepository->update($id, [
            'is_featured' => !$campaign->is_featured
        ]);

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => $campaign->is_featured ? 'Campaign featured successfully' : 'Campaign unfeatured successfully'
        ]);
    }

    /**
     * Get campaigns that need admin attention.
     */
    public function needsAttention(): AnonymousResourceCollection
    {
        // Get pending campaigns and campaigns that might need review
        $campaigns = Campaign::with(['user'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                            ->where('end_date', '<', now());
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Get campaign management statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_campaigns' => Campaign::count(),
            'by_status' => Campaign::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'pending_approval' => Campaign::where('status', 'pending')->count(),
            'featured_campaigns' => Campaign::where('is_featured', true)->count(),
            'expired_campaigns' => Campaign::where('status', 'active')
                ->where('end_date', '<', now())
                ->count(),
            'success_rate' => Campaign::whereRaw('current_amount >= goal_amount')->count() / max(Campaign::count(), 1) * 100,
            'recent_activity' => $this->getRecentActivity(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk update campaign statuses.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_ids' => 'required|array',
            'campaign_ids.*' => 'integer|exists:campaigns,id',
            'status' => ['required', Rule::in(['draft', 'pending', 'active', 'completed', 'cancelled'])],
            'reason' => 'nullable|string|max:500'
        ]);

        $updatedCount = $this->campaignRepository->bulkUpdateStatus(
            $request->campaign_ids,
            $request->status,
            $request->input('reason'),
            $request->status === 'active' ? auth('api')->id() : null
        );

        return response()->json([
            'message' => "Successfully updated {$updatedCount} campaigns",
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Check if campaign can be enabled.
     */
    protected function canBeEnabled(Campaign $campaign): bool
    {
        return !empty($campaign->title) &&
               !empty($campaign->description) &&
               $campaign->goal_amount > 0 &&
               $campaign->start_date <= now() &&
               $campaign->end_date >= now();
    }

    /**
     * Get recent admin activity.
     */
    protected function getRecentActivity(): array
    {
        return Campaign::with(['user', 'approvedBy'])
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($campaign) {
                return [
                    'campaign_id' => $campaign->id,
                    'campaign_title' => $campaign->title,
                    'action' => 'approved',
                    'admin' => $campaign->approvedBy?->name,
                    'created_at' => $campaign->approved_at->toDateTimeString(),
                ];
            })
            ->toArray();
    }
} 