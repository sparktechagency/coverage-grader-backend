<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{

    protected UserService $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->userService->getAll();
        return UserResource::collection($users);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->userService->getById($id, ['roles']);
        return new UserResource($user);
    }


    //assing role to user
    public function assignRole(AssignRoleRequest $request, string $id)
    {
         $user = $this->userService->assignRole($id, $request->validated('role'));

        return response_success(
            'User role updated successfully.',
            new UserResource($user->load('roles'))
        );
    }

    //update user status
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        
        $user->status = $request->status;
        $user->save();
        if($request->status == 'inactive'){
            // Revoke all tokens
            $user->tokens()->delete();
        }
        return response_success('User status updated successfully.', new UserResource($user));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->userService->delete($id);
        return response_success('User deleted successfully');
    }
}
