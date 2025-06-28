<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            $status = $request->get('status');

            $cacheKey = "users_list_" . md5(serialize($request->all()));

            $users = Cache::remember($cacheKey, 300, function () use ($perPage, $search, $status) {
                $query = User::with(['tasks' => function($q) {
                    $q->select('id', 'user_id', 'status');
                }]);

                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                if ($status) {
                    $query->where('status', $status);
                }

                return $query->latest()->paginate($perPage);
            });

            return $this->paginatedResponse($users, 'Users retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            DB::commit();

            // Clear users cache
            Cache::forget('users_list_*');

            return $this->successResponse(
                $user->fresh(),
                'User created successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    public function show(User $user): JsonResponse
    {
        try {
            $cacheKey = "user_details_{$user->id}";

            $userWithTasks = Cache::remember($cacheKey, 300, function () use ($user) {
                return $user->load(['tasks' => function($query) {
                    $query->latest();
                }]);
            });

            return $this->successResponse(
                $userWithTasks,
                'User retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    public function update(UserStoreRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            DB::commit();

            // Clear cache
            Cache::forget("user_details_{$user->id}");
            Cache::forget('users_list_*');

            return $this->successResponse(
                $user->fresh(),
                'User updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user->delete();

            DB::commit();

            // Clear cache
            Cache::forget("user_details_{$user->id}");
            Cache::forget('users_list_*');

            return $this->successResponse(null, 'User deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    public function tasks(User $user): JsonResponse
    {
        try {
            $cacheKey = "user_tasks_{$user->id}";

            $tasks = Cache::remember($cacheKey, 300, function () use ($user) {
                return $user->tasks()->latest()->get();
            });

            return $this->successResponse(
                $tasks,
                'User tasks retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user tasks: ' . $e->getMessage(), 500);
        }
    }
}
