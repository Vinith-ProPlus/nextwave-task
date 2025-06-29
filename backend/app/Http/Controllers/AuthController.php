<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'The name field is required.',
                'name.max' => 'The name may not be greater than 255 characters.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = Auth::login($user);

            return $this->successResponse([
                'user' => $user,
                'token' => $token
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to register user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'password.required' => 'The password field is required.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $credentials = $request->only(['email', 'password']);

            if (!$token = Auth::attempt($credentials)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            $user = Auth::user();

            return $this->successResponse([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to login: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            Auth::logout();

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to logout: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            $user = Auth::user();

            return $this->successResponse($user, 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $token = Auth::refresh();
            $user = Auth::user();

            return $this->successResponse([
                'user' => $user,
                'token' => $token
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to refresh token: ' . $e->getMessage(), 500);
        }
    }
}
