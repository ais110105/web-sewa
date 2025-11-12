<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProfileServiceInterface;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected ProfileServiceInterface $profileService;

    public function __construct(ProfileServiceInterface $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Display the authenticated user's profile page.
     */
    public function index(): View
    {
        $profile = $this->profileService->getMyProfile();

        return view('profile.index', compact('profile'));
    }

    /**
     * Get the authenticated user's profile data.
     */
    public function show(): JsonResponse
    {
        $profile = $this->profileService->getMyProfile();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $profile?->id,
                'user_id' => $profile?->user_id ?? auth()->id(),
                'full_name' => $profile?->full_name,
                'phone' => $profile?->phone,
                'address' => $profile?->address,
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->profileService->updateMyProfile($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
}
