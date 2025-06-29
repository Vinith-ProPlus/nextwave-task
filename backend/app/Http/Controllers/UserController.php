<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\Filterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse, Filterable;

    /**
     * Get all users with filtering, sorting, and pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Map start_date/end_date to created_at_from/created_at_to for compatibility
            $input = $request->all();
            $hasStart = isset($input['start_date']);
            $hasEnd = isset($input['end_date']);
            if ($hasStart) {
                $input['created_at_from'] = $input['start_date'];
            }
            if ($hasEnd) {
                $input['created_at_to'] = $input['end_date'];
            }
            $request->replace($input);

            // Validate request parameters first
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'role' => 'nullable|in:admin,user,manager',
                'is_active' => 'nullable|in:true,false,1,0,yes,no,on,off',
                'created_at_from' => 'nullable|date',
                'created_at_to' => 'nullable|date',
                'sort_by' => 'nullable|in:name,email,role,created_at',
                'sort_order' => 'nullable|in:asc,desc',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            // Custom validation for date range
            $validator->after(function ($validator) use ($request, $hasStart, $hasEnd) {
                if ($request->filled('created_at_from') && $request->filled('created_at_to')) {
                    if (strtotime($request->created_at_to) < strtotime($request->created_at_from)) {
                        $validator->errors()->add('created_at_to', 'The created_at_to (or end_date) must be after or equal to created_at_from (or start_date).');
                        if ($hasEnd) {
                            $validator->errors()->add('end_date', 'The end_date must be after or equal to start_date.');
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = User::query();

            // Define filters configuration
            $filters = [
                'role' => ['type' => 'exact'],
                'is_active' => ['type' => 'boolean'],
                // Support both start_date/end_date and created_at_from/created_at_to
                'created_at_from' => ['type' => 'date_range', 'start_field' => 'created_at', 'operator' => '>='],
                'created_at_to' => ['type' => 'date_range', 'end_field' => 'created_at', 'operator' => '<='],
                'start_date' => ['type' => 'date_range', 'start_field' => 'created_at', 'operator' => '>='],
                'end_date' => ['type' => 'date_range', 'end_field' => 'created_at', 'operator' => '<='],
            ];

            $searchableFields = ['name', 'email'];
            $sortableFields = ['name', 'email', 'role', 'created_at'];

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

            return $this->successResponse($result['data'], 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'nullable|in:admin,user,manager',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'The name field is required.',
                'name.max' => 'The name may not be greater than 255 characters.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'role.in' => 'The role must be one of: admin, user, manager.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'is_active' => $request->is_active ?? true,
            ]);

            return $this->successResponse($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'role' => 'nullable|in:admin,user,manager',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'The name field is required.',
                'name.max' => 'The name may not be greater than 255 characters.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'password.min' => 'The password must be at least 8 characters.',
                'role.in' => 'The role must be one of: admin, user, manager.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $request->only(['name', 'email', 'role', 'is_active']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return $this->successResponse($user, 'User updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            $user->delete();

            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }
}
