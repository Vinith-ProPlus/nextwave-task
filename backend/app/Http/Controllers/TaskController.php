<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\Filterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    use ApiResponse, Filterable;

    /**
     * Get all tasks with filtering, sorting, and pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Task::query();

            // Define filters configuration
            $filters = [
                'status' => ['type' => 'exact'],
                'priority' => ['type' => 'exact'],
                'due_date_from' => ['type' => 'date_range', 'start_field' => 'due_date', 'operator' => '>='],
                'due_date_to' => ['type' => 'date_range', 'end_field' => 'due_date', 'operator' => '<='],
            ];

            $searchableFields = ['title', 'description'];
            $sortableFields = ['title', 'status', 'priority', 'due_date', 'created_at'];

            // Apply filters, sorting, and pagination
            $result = $this->applyFilters(
                $query,
                $request,
                $filters,
                $searchableFields,
                $sortableFields,
                'created_at',
                'desc',
                15
            );

            if (!$result['success']) {
                return $this->validationErrorResponse($result['errors']);
            }

            return $this->successResponse($result['data'], 'Tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve tasks: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new task
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|in:pending,in_progress,completed,cancelled',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'due_date' => 'nullable|date|after:today',
                'user_id' => 'nullable|exists:users,id',
            ], [
                'title.required' => 'The title field is required.',
                'title.max' => 'The title may not be greater than 255 characters.',
                'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
                'priority.in' => 'The priority must be one of: low, medium, high, urgent.',
                'due_date.after' => 'The due date must be a date after today.',
                'user_id.exists' => 'The selected assigned user is invalid.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status ?? 'pending',
                'priority' => $request->priority ?? 'medium',
                'due_date' => $request->due_date,
                'user_id' => $request->user_id ?? Auth::id(),
                'assigned_by' => Auth::id(),
            ]);

            return $this->successResponse($task, 'Task created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            return $this->successResponse($task, 'Task retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a task
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled',
                'priority' => 'sometimes|required|in:low,medium,high,urgent',
                'due_date' => 'nullable|date|after:today',
                'user_id' => 'nullable|exists:users,id',
            ], [
                'title.required' => 'The title field is required.',
                'title.max' => 'The title may not be greater than 255 characters.',
                'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
                'priority.in' => 'The priority must be one of: low, medium, high, urgent.',
                'due_date.after' => 'The due date must be a date after today.',
                'user_id.exists' => 'The selected assigned user is invalid.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $request->only(['title', 'description', 'status', 'priority', 'due_date', 'user_id']);

            // Set completed_at when status is completed
            if (isset($data['status']) && $data['status'] === 'completed') {
                $data['completed_at'] = Carbon::now();
            }

            $task->update($data);

            return $this->successResponse($task, 'Task updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update task status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->notFoundResponse('Task not found');
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed,cancelled',
            ], [
                'status.required' => 'The status field is required.',
                'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = ['status' => $request->status];

            // Set completed_at when status is completed
            if ($request->status === 'completed') {
                $data['completed_at'] = Carbon::now();
            }

            $task->update($data);

            return $this->successResponse($task, 'Task status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $task = Task::find($id);

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
     * Get tasks for a specific user
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTasks($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            $tasks = Task::where('user_id', $userId)
                ->orWhere('assigned_by', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($tasks, 'User tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user tasks: ' . $e->getMessage(), 500);
        }
    }
}
