<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function showLogin()
    {
        if ($this->apiService->isAuthenticated()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if ($this->apiService->isAuthenticated()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $result = $this->apiService->login($request->email, $request->password);

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back! You have been successfully logged in.');
        }

        return redirect()->back()
            ->withErrors(['email' => $result['message'] ?? 'Invalid credentials'])
            ->withInput($request->except('password'));
    }

    public function register(Request $request)
    {
        $result = $this->apiService->register([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        Log::info("Registration result", $result);

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', 'Account created successfully! Welcome to NextWave Task Management.');
        }

        $errors = [];
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $fieldErrors) {
                $errors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
            }
        } else {
            $errors['email'] = $result['message'] ?? 'Registration failed';
        }

        return redirect()->back()
            ->withErrors($errors)
            ->withInput($request->except(['password', 'password_confirmation']));
    }

    public function logout()
    {
        $this->apiService->logout();
        return redirect()->route('login')
            ->with('success', 'You have been successfully logged out.');
    }
}
