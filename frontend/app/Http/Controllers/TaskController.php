<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $filters = $request->only(['search', 'status', 'priority', 'sort_by', 'sort_order', 'page', 'per_page']);
        $result = $this->apiService->getTasks($filters);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Get users for assignment dropdown
        $usersResult = $this->apiService->getUsers(['per_page' => 1000]);
        $users = $usersResult['success'] ? $usersResult['data']['data'] : [];

        return view('tasks.index', [
            'tasks' => $result['data']['data'],
            'pagination' => $result['data']['pagination'],
            'filters' => $result['data']['filters'] ?? [],
            'appliedFilters' => $filters,
            'users' => $users
        ]);
    }

    public function create()
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        // Get users for assignment dropdown
        $usersResult = $this->apiService->getUsers(['per_page' => 1000]);
        $users = $usersResult['success'] ? $usersResult['data']['data'] : [];

        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'user_id' => 'nullable|integer',
        ], [
            'title.required' => 'Title is required',
            'title.max' => 'Title cannot exceed 255 characters',
            'status.in' => 'Invalid status selected',
            'priority.in' => 'Invalid priority selected',
            'due_date.after' => 'Due date must be a future date',
            'user_id.integer' => 'Invalid user selected',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->apiService->createTask([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending',
            'priority' => $request->priority ?? 'medium',
            'due_date' => $request->due_date,
            'user_id' => $request->user_id,
        ]);

        if ($result['success']) {
            return redirect()->route('tasks.index')
                ->with('success', 'Task created successfully!');
        }

        $errors = [];
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $fieldErrors) {
                $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
            }
        } else {
            $errors['title'] = $result['message'] ?? 'Failed to create task';
        }

        return redirect()->back()
            ->withErrors($errors)
            ->withInput();
    }

    public function show($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->getTask($id);

        if (!$result['success']) {
            return redirect()->route('tasks.index')
                ->with('error', $result['message'] ?? 'Task not found');
        }

        return view('tasks.show', ['task' => $result['data']]);
    }

    public function edit($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->getTask($id);

        if (!$result['success']) {
            return redirect()->route('tasks.index')
                ->with('error', $result['message'] ?? 'Task not found');
        }

        // Get users for assignment dropdown
        $usersResult = $this->apiService->getUsers(['per_page' => 1000]);
        $users = $usersResult['success'] ? $usersResult['data']['data'] : [];

        return view('tasks.edit', [
            'task' => $result['data'],
            'users' => $users
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'user_id' => 'nullable|integer',
        ], [
            'title.required' => 'Title is required',
            'title.max' => 'Title cannot exceed 255 characters',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
            'priority.required' => 'Priority is required',
            'priority.in' => 'Invalid priority selected',
            'due_date.after' => 'Due date must be a future date',
            'user_id.integer' => 'Invalid user selected',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->apiService->updateTask($id, [
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'user_id' => $request->user_id,
        ]);

        if ($result['success']) {
            return redirect()->route('tasks.index')
                ->with('success', 'Task updated successfully!');
        }

        $errors = [];
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $fieldErrors) {
                $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
            }
        } else {
            $errors['title'] = $result['message'] ?? 'Failed to update task';
        }

        return redirect()->back()
            ->withErrors($errors)
            ->withInput();
    }

    public function updateStatus(Request $request, $id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ], [
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $result = $this->apiService->updateTaskStatus($id, $request->status);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', 'Task status updated successfully!');
        }

        return redirect()->back()
            ->with('error', $result['message'] ?? 'Failed to update task status');
    }

    public function destroy($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->deleteTask($id);

        if ($result['success']) {
            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully!');
        }

        return redirect()->route('tasks.index')
            ->with('error', $result['message'] ?? 'Failed to delete task');
    }
}
