<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequset;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Profile
 * Manage the authenticated user's profile information.
 */
class ProfileController extends Controller
{
    use FileUploadTrait;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles');
        return new UserResource($user);
    }

    public function updateProfile(UpdateProfileRequset $request)
    {
        try {
            $user = $this->authService->updateProfile( $request->user(), $request);
            return response_success('Profile updated successfully.', new UserResource($user));
        } catch (ValidationException $e) {
            return response_error('Validation failed.', $e->errors(), 422);
        }
    }
}
