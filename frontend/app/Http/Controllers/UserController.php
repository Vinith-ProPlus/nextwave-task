<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use App\Services\TokenExpiredException;
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
        try {
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
            return view('users.create');
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
            $result = $this->apiService->getUser($id);
            if (!$result['success']) {
                return redirect()->route('users.index')
                    ->with('error', $result['message'] ?? 'User not found');
            }
            return view('users.edit', ['user' => $result['data']]);
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
            $result = $this->apiService->deleteUser($id);
            if ($result['success']) {
                return redirect()->route('users.index')
                    ->with('success', 'User deleted successfully!');
            }
            return redirect()->route('users.index')
                ->with('error', $result['message'] ?? 'Failed to delete user');
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    // View current user's profile
    public function profile()
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $userId = auth()->user()->id;
            $result = $this->apiService->getUser($userId);
            if (!$result['success']) {
                return redirect()->route('dashboard')->with('error', $result['message'] ?? 'User not found');
            }
            return view('users.profile', ['user' => $result['data']]);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    // Show edit form for current user's profile
    public function profileEdit()
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $userId = auth()->user()->id;
            $result = $this->apiService->getUser($userId);
            if (!$result['success']) {
                return redirect()->route('profile')->with('error', $result['message'] ?? 'User not found');
            }
            return view('users.profile_edit', ['user' => $result['data']]);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    // Update current user's profile
    public function profileUpdate(Request $request)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $userId = auth()->user()->id;
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            $result = $this->apiService->updateUser($userId, $userData);
            if ($result['success']) {
                return redirect()->route('profile')->with('success', 'Profile updated successfully!');
            }
            $errors = [];
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $field => $fieldErrors) {
                    $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
                }
            } else {
                $errors['email'] = $result['message'] ?? 'Failed to update profile';
            }
            return redirect()->back()->withErrors($errors)->withInput();
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    // Change current user's password
    public function changePassword(Request $request)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $userId = auth()->user()->id;
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6|confirmed',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $result = $this->apiService->updateUser($userId, [
                'password' => $request->password,
            ]);
            if ($result['success']) {
                return redirect()->route('profile')->with('success', 'Password changed successfully!');
            }
            $errors = [];
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $field => $fieldErrors) {
                    $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
                }
            } else {
                $errors['password'] = $result['message'] ?? 'Failed to change password';
            }
            return redirect()->back()->withErrors($errors)->withInput();
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }
}
