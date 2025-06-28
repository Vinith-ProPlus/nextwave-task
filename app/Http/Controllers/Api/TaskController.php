<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Models\Task;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status');
            $priority = $request->get('priority');
            $userId = $request->get('user_id');

            $cacheKey = "tasks_list_" . md5(serialize($request->all()));

            $tasks = Cache::remember($cacheKey, 300, function () use ($perPage, $status, $priority, $userId) {
                $query = Task::with('user:id,name,email');

                if ($status) {
                    $query->byStatus($status);
                }

                if ($priority) {
                    $query->byPriority($priority);
                }

                if ($userId) {
                    $query->where('user_id', $userId);
                }

                return $query->latest()->paginate($perPage);
            });

            return $this->paginatedResponse($tasks, 'Tasks retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve tasks: ' . $e->getMessage(), 500);
        }
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $task = Task::create($request->validated());

            DB::commit();

            // Clear tasks cache
            Cache::forget('tasks_list_*');
            Cache::forget("user_tasks_{$task->user_id}");

            return $this->successResponse(
                $task->load('user:id,name,email'),
                'Task created successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create task: ' . $e->getMessage(), 500);
        }
    }

    public function show(Task $task): JsonResponse
    {
        try {
            return $this->successResponse(
                $task->load('user:id,name,email'),
                'Task retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve task: ' . $e->getMessage(), 500);
        }
    }

    public function update(TaskStoreRequest $request, Task $task): JsonResponse
    {
        DB::beginTransaction();

        try {
            $task->update($request->validated());

            DB::commit();

            // Clear cache
            Cache::forget('tasks_list_*');
            Cache::forget("user_tasks_{$task->user_id}");

            return $this->successResponse(
                $task->fresh(['user:id,name,email']),
                'Task updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update task: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        DB::beginTransaction();

        try {
            $task->delete();

            DB::commit();

            // Clear cache
            Cache::forget('tasks_list_*');
            Cache::forget("user_tasks_{$task->user_id}");

            return $this->successResponse(null, 'Task deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete task: ' . $e->getMessage(), 500);
        }
    }
}
