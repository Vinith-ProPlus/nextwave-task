<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use App\Services\TokenExpiredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DataTableHelper;

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

            // DataTables AJAX request
            if ($request->ajax() && $request->has('draw')) {
                $columnMap = [
                    0 => 'created_at', // Map ID column to created_at for backend compatibility
                    1 => 'name',
                    2 => 'email',
                    3 => 'is_active',
                    4 => 'created_at',
                ];
                $dt = DataTableHelper::parseRequest($request, $columnMap);
                $result = $this->apiService->getUsers($dt['filters']);
                return DataTableHelper::formatResponse($dt['draw'], $result, function($user) {
                    return [
                        $user['id'],
                        '<div class="d-flex align-items-center">'
                        .'<div class="bg-primary bg-gradient rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">'
                        .'<span class="text-white fw-bold">'.strtoupper(substr($user['name'], 0, 1)).'</span>'
                        .'</div>'
                        .'<div><strong>'.$user['name'].'</strong></div>'
                        .'</div>',
                        $user['email'],
                        $user['is_active']
                            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>'
                            : '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inactive</span>',
                        '<span title="'.\Carbon\Carbon::parse($user['created_at'])->format('F d, Y \\a\\t g:i A').'">'.\Carbon\Carbon::parse($user['created_at'])->diffForHumans().'</span>',
                        '<div class="btn-group" role="group">'
                        .'<a href="'.route('users.show', $user['id']).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>'
                        .'<a href="'.route('users.edit', $user['id']).'" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>'
                        .'<button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete(\''.route('users.destroy', $user['id']).'\', \'Delete User\', \'Are you sure you want to delete '.e($user['name']).'? This action cannot be undone.\')"><i class="fas fa-trash"></i></button>'
                        .'</div>'
                    ];
                });
            }

            // Regular page load - show the view (no users data, DataTables will fetch via AJAX)
            return view('users.index');
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

            // Get current user profile from API
            $result = $this->apiService->getProfile();
            if (!$result['success']) {
                return redirect()->route('dashboard')->with('error', $result['message'] ?? 'Failed to load profile');
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

            // Get current user profile from API
            $result = $this->apiService->getProfile();
            if (!$result['success']) {
                return redirect()->route('profile')->with('error', $result['message'] ?? 'Failed to load profile');
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

            // Get current user profile first to get the user ID
            $profileResult = $this->apiService->getProfile();
            if (!$profileResult['success']) {
                return redirect()->route('profile')->with('error', 'Failed to load profile');
            }

            $userId = $profileResult['data']['id'];
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            $result = $this->apiService->updateUser($userId, $userData);
            if ($result['success']) {
                // Clear profile cache to ensure fresh data
                $this->apiService->clearProfileCache();
                // Force refresh the profile data in the session
                $freshProfile = $this->apiService->getProfileFresh();
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

            // Get current user profile first to get the user ID
            $profileResult = $this->apiService->getProfile();
            if (!$profileResult['success']) {
                return redirect()->route('profile')->with('error', 'Failed to load profile');
            }

            $userId = $profileResult['data']['id'];
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
                // Clear profile cache to ensure fresh data
                $this->apiService->clearProfileCache();
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
