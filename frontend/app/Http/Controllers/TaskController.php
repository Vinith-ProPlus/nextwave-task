<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TokenExpiredException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\DataTableHelper;

class TaskController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }

            // DataTables AJAX request
            if ($request->ajax() && $request->has('draw')) {
                $columnMap = [
                    0 => 'created_at', // Map ID column to created_at for backend compatibility
                    1 => 'title',
                    2 => 'user_id',
                    3 => 'assigned_by',
                    4 => 'status',
                    5 => 'priority',
                    6 => 'due_date',
                    7 => 'created_at',
                ];
                $dt = DataTableHelper::parseRequest($request, $columnMap);
                $result = $this->apiService->getTasks($dt['filters']);
                return DataTableHelper::formatResponse($dt['draw'], $result, function($task) {
                    return [
                        $task['id'],
                        '<div><strong>' . e(Str::limit($task['title'], 40)) . '</strong>' .
                        ($task['description'] ? '<br><small class="text-muted">' . e(Str::limit($task['description'], 50)) . '</small>' : '') . '</div>',
                        (isset($task['user']) && $task['user'])
                            ? '<div class="d-flex align-items-center"><div class="bg-primary bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><span class="text-white fw-bold small">' . strtoupper(substr($task['user']['name'], 0, 1)) . '</span></div><span>' . e($task['user']['name']) . '</span></div>'
                            : '<span class="text-muted">Unassigned</span>',
                        (isset($task['assigned_by']) && $task['assigned_by'])
                            ? '<div class="d-flex align-items-center"><div class="bg-success bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;"><span class="text-white fw-bold small">' . strtoupper(substr($task['assigned_by']['name'], 0, 1)) . '</span></div><span>' . e($task['assigned_by']['name']) . '</span></div>'
                            : '<span class="text-muted">System</span>',
                        '<span class="badge status-' . e($task['status']) . '">' . ucfirst(str_replace('_', ' ', $task['status'])) . '</span>',
                        '<span class="badge priority-' . e($task['priority']) . '">' . ucfirst($task['priority']) . '</span>',
                        $task['due_date']
                            ? '<span title="' . \Carbon\Carbon::parse($task['due_date'])->format('F d, Y \\a\\t g:i A') . '">' . \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') . (\Carbon\Carbon::parse($task['due_date'])->isPast() && $task['status'] !== 'completed' ? ' <i class="fas fa-exclamation-triangle ms-1 text-danger"></i>' : '') . '</span>'
                            : '<span class="text-muted">No due date</span>',
                        '<span title="' . \Carbon\Carbon::parse($task['created_at'])->format('F d, Y \\a\\t g:i A') . '">' . \Carbon\Carbon::parse($task['created_at'])->diffForHumans() . '</span>',
                        '<div class="btn-group" role="group">'
                        .'<a href="'.route('tasks.show', $task['id']).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>'
                        .'<a href="'.route('tasks.edit', $task['id']).'" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>'
                        .'<button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete(\''.route('tasks.destroy', $task['id']).'\', \'Delete Task\', \'Are you sure you want to delete this task? This action cannot be undone.\')"><i class="fas fa-trash"></i></button>'
                        .'</div>'
                    ];
                });
            }

            // Regular page load - show the view (no tasks data, DataTables will fetch via AJAX)
            return view('tasks.index');
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function create()
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $usersResult = $this->apiService->getUsers(['per_page' => 1000]);
            $users = $usersResult['success'] ? $usersResult['data']['data'] : [];
            return view('tasks.create', compact('users'));
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function store(Request $request)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $result = $this->apiService->createTask($request->all());
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
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function show($id)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $result = $this->apiService->getTask($id);
            if (!$result['success']) {
                return redirect()->route('tasks.index')
                    ->with('error', $result['message'] ?? 'Task not found');
            }
            return view('tasks.show', ['task' => $result['data']]);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function edit($id)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $result = $this->apiService->getTask($id);
            if (!$result['success']) {
                return redirect()->route('tasks.index')
                    ->with('error', $result['message'] ?? 'Task not found');
            }
            $usersResult = $this->apiService->getUsers(['per_page' => 100000]);
            $users = $usersResult['success'] ? $usersResult['data']['data'] : [];
            return view('tasks.edit', [
                'task' => $result['data'],
                'users' => $users
            ]);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
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
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        logger("updateStatus from controller: " . json_encode($request->all()));
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $result = $this->apiService->updateTaskStatus($id, $request->status);
            logger("updateStatus response from controller: " . json_encode($result));
            if ($result['success']) {
                // Clear validation errors from session
                session()->forget('_old_input');
                session()->forget('errors');
                return redirect()->route('tasks.show', $id)->with('success', 'Task status updated successfully!');
            }
            return redirect()->back()
                ->with('error', $result['message'] ?? 'Failed to update task status');
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    public function destroy($id)
    {
        try {
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
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }
}
