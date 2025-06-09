<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = auth('api')->user()->load(['campaigns', 'donations.campaign']);

        return response()->json([
            'data' => new UserResource($user),
            'stats' => [
                'total_campaigns' => $user->campaigns->count(),
                'active_campaigns' => $user->campaigns->where('status', 'active')->count(),
                'total_donated' => $user->donations->where('status', 'completed')->sum('amount'),
                'total_donations' => $user->donations->where('status', 'completed')->count(),
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $data = $request->validated();

        // Handle password update
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Get user's donation history.
     */
    public function donations(): JsonResponse
    {
        $donations = auth('api')->user()
            ->donations()
            ->with('campaign')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($donations);
    }

    /**
     * Get user's campaign history.
     */
    public function campaigns(): JsonResponse
    {
        $campaigns = auth('api')->user()
            ->campaigns()
            ->withCount('donations')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($campaigns);
    }
} 