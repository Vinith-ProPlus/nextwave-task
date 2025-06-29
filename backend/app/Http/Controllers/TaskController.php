<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of tasks for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $tasks = Task::where('user_id', $user->id)->paginate(10);
            return $this->successResponse($tasks, 'Tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve tasks: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created task
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();

            $task = Task::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status ?? 'pending',
                'priority' => $request->priority ?? 'medium',
                'due_date' => $request->due_date,
            ]);

            return $this->successResponse($task, 'Task created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $task = Task::where('user_id', $user->id)->find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            return $this->successResponse($task, 'Task retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified task
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();
            $task = Task::where('user_id', $user->id)->find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            $data = $request->only(['title', 'description', 'status', 'priority', 'due_date']);

            // If status is being updated to completed, set completed_at
            if (isset($data['status']) && $data['status'] === 'completed') {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }

            $task->update($data);

            return $this->successResponse($task, 'Task updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update task status (PATCH endpoint)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();
            $task = Task::where('user_id', $user->id)->find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            $data = ['status' => $request->status];

            if ($request->status === 'completed') {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }

            $task->update($data);

            return $this->successResponse($task, 'Task status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $task = Task::where('user_id', $user->id)->find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            $task->delete();

            return $this->successResponse(null, 'Task deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get tasks for a specific user (admin only)
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTasks($userId)
    {
        try {
            $user = Auth::user();

            // Check if authenticated user is admin or the same user
            if (!$user->isAdmin() && $user->id != $userId) {
                return $this->forbiddenResponse('Access denied');
            }

            $targetUser = User::find($userId);
            if (!$targetUser) {
                return $this->notFoundResponse('User not found');
            }

            $tasks = Task::where('user_id', $userId)->paginate(10);

            return $this->successResponse([
                'user' => $targetUser,
                'tasks' => $tasks,
            ], 'User tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user tasks: ' . $e->getMessage(), 500);
        }
    }
}
