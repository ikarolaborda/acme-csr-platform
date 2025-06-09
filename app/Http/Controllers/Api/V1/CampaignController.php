<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateCampaignAction;
use App\Actions\UpdateCampaignAction;
use App\Contracts\CampaignRepositoryInterface;
use App\DTOs\CampaignData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected CreateCampaignAction $createCampaignAction,
        protected UpdateCampaignAction $updateCampaignAction
    ) {}

    /**
     * Display a listing of campaigns.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'category', 'status']);
        $campaigns = $this->campaignRepository->paginate($request->get('per_page', 12), $filters);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('campaigns', 'public');
            $data['featured_image'] = $path;
        }
        
        // Generate unique slug
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['user_id'] = auth('api')->id();

        $campaignData = CampaignData::from($data);
        $campaign = $this->createCampaignAction->execute($campaignData);

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign created successfully'
        ], 201);
    }

    /**
     * Display the specified campaign.
     */
    public function show(string $slug): JsonResponse
    {
        $campaign = $this->campaignRepository->findBySlug($slug);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        // Increment views
        $campaign->incrementViews();

        return response()->json([
            'data' => new CampaignResource($campaign->load(['user', 'donations.user']))
        ]);
    }

    /**
     * Update the specified campaign.
     */
    public function update(UpdateCampaignRequest $request, int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        // Check ownership
        if ($campaign->user_id !== auth('api')->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($campaign->featured_image) {
                Storage::disk('public')->delete($campaign->featured_image);
            }
            
            $image = $request->file('image');
            $path = $image->store('campaigns', 'public');
            $data['featured_image'] = $path;
        }

        $campaignData = CampaignData::from(array_merge($campaign->toArray(), $data));
        $campaign = $this->updateCampaignAction->execute($campaign, $campaignData);

        return response()->json([
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign updated successfully'
        ]);
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'message' => 'Campaign not found'
            ], 404);
        }

        // Check ownership
        if ($campaign->user_id !== auth('api')->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete image
        if ($campaign->featured_image) {
            Storage::disk('public')->delete($campaign->featured_image);
        }

        $this->campaignRepository->delete($id);

        return response()->json([
            'message' => 'Campaign deleted successfully'
        ], 204);
    }

    /**
     * Get campaigns created by the authenticated user.
     */
    public function userCampaigns(Request $request): AnonymousResourceCollection
    {
        $campaigns = $this->campaignRepository->getByUser(auth('api')->id());

        // Since getByUser returns a Collection, we need to paginate manually
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);
        $items = $campaigns->forPage($page, $perPage);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $campaigns->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return CampaignResource::collection($paginator);
    }

    /**
     * Get featured campaigns.
     */
    public function featured(): AnonymousResourceCollection
    {
        $campaigns = $this->campaignRepository->getFeatured(6);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Get trending campaigns.
     */
    public function trending(): AnonymousResourceCollection
    {
        $campaigns = $this->campaignRepository->getTrending(6);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Get campaigns ending soon.
     */
    public function endingSoon(): AnonymousResourceCollection
    {
        $campaigns = $this->campaignRepository->getEndingSoon(7, 6);

        return CampaignResource::collection($campaigns);
    }

    /**
     * Generate a unique slug for the campaign.
     */
    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Campaign::where('slug', 'LIKE', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }
} 