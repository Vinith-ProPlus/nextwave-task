<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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

        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_order', 'page', 'per_page']);
        $result = $this->apiService->getUsers($filters);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return view('users.index', [
            'users' => $result['data']['data'],
            'pagination' => $result['data']['pagination'],
            'filters' => $result['data']['filters'] ?? [],
            'appliedFilters' => $filters
        ]);
    }

    public function create()
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $result = $this->apiService->createUser([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => $request->has('is_active'),
        ]);

        if ($result['success']) {
            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        }

        $errors = [];
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $fieldErrors) {
                $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
            }
        } else {
            $errors['email'] = $result['message'] ?? 'Failed to create user';
        }

        return redirect()->back()
            ->withErrors($errors)
            ->withInput($request->except('password'));
    }

    public function show($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->getUser($id);

        if (!$result['success']) {
            return redirect()->route('users.index')
                ->with('error', $result['message'] ?? 'User not found');
        }

        // Get user's tasks
        $userTasks = $this->apiService->getUserTasks($id);

        return view('users.show', [
            'user' => $result['data'],
            'tasks' => $userTasks['success'] ? $userTasks['data'] : []
        ]);
    }

    public function edit($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->getUser($id);

        if (!$result['success']) {
            return redirect()->route('users.index')
                ->with('error', $result['message'] ?? 'User not found');
        }

        return view('users.edit', ['user' => $result['data']]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:8',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = $request->password;
        }

        $result = $this->apiService->updateUser($id, $userData);

        if ($result['success']) {
            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');
        }

        $errors = [];
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $fieldErrors) {
                $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
            }
        } else {
            $errors['email'] = $result['message'] ?? 'Failed to update user';
        }

        return redirect()->back()
            ->withErrors($errors)
            ->withInput($request->except('password'));
    }

    public function destroy($id)
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $result = $this->apiService->deleteUser($id);

        if ($result['success']) {
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        }

        return redirect()->route('users.index')
            ->with('error', $result['message'] ?? 'Failed to delete user');
    }
}
