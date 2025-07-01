<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ApiService
{
    protected $client;
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = env('BACKEND_API_URL', 'http://localhost:8000/api');
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
        $this->token = Session::get('jwt_token');
    }

    /**
     * Set authentication token
     */
    public function setToken($token)
    {
        $this->token = $token;
        Session::put('jwt_token', $token);
    }

    /**
     * Get authentication token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Clear authentication token
     */
    public function clearToken()
    {
        $this->token = null;
        Session::forget('jwt_token');
    }

    /**
     * Make authenticated request
     */
    protected function makeRequest($method, $endpoint, $data = null, $useCache = false, $cacheKey = null, $cacheTime = 300)
    {
        $fullUrl = $this->baseUrl . $endpoint;

        Log::info("Making API request to: {$fullUrl}", [
            'method' => $method,
            'data' => $data
        ]);

        $headers = [];

        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        // Check cache for GET requests
        if ($useCache && $method === 'GET' && $cacheKey) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            $options = ['headers' => $headers];

            if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['json'] = $data;
            }

            Log::info("Making API request to: {$fullUrl}", [
                'method' => $method,
                'data' => $data,
                'headers' => $headers
            ]);

            $response = $this->client->request($method, $fullUrl, $options);
            $result = json_decode($response->getBody(), true);

            Log::info("API response received", [
                'status_code' => $response->getStatusCode(),
                'result' => $result
            ]);

            // Cache successful GET responses
            if ($useCache && $method === 'GET' && $cacheKey && $response->getStatusCode() === 200) {
                Cache::put($cacheKey, $result, $cacheTime);
            }

            return $result;

        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? json_decode($response->getBody(), true) : null;
            $status = $response ? $response->getStatusCode() : 500;
            $message = $errorBody['message'] ?? '';

            // Token expired/invalid/unauthorized handling
            if (in_array($status, [401, 403]) || stripos($message, 'token') !== false || stripos($message, 'unauthorized') !== false) {
                $this->clearToken();
                throw new TokenExpiredException($message ?: 'Session expired, please log in again.');
            }

            Log::error("API request failed", [
                'url' => $fullUrl,
                'method' => $method,
                'status_code' => $status,
                'error' => $e->getMessage(),
                'response' => $errorBody
            ]);

            return [
                'success' => false,
                'message' => $message ?: 'API request failed',
                'errors' => $errorBody['errors'] ?? null,
                'status_code' => $status
            ];
        } catch (\Exception $e) {
            Log::error("Network error", [
                'url' => $fullUrl,
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Network error: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    // Authentication Methods
    public function login($email, $password)
    {
        $result = $this->makeRequest('POST', '/login', [
            'email' => $email,
            'password' => $password
        ]);

        if ($result['success'] && isset($result['data']['token'])) {
            $this->setToken($result['data']['token']);
        }
        Log::info("Login response from api service: " . json_encode($result));

        return $result;
    }

    public function register($userData)
    {
        $result = $this->makeRequest('POST', '/register', $userData);

        if ($result['success'] && isset($result['data']['token'])) {
            $this->setToken($result['data']['token']);
        }

        return $result;
    }

    public function getProfile()
    {
        return $this->makeRequest('GET', '/me', null, true, 'user_profile_' . $this->token, 300);
    }

    public function logout()
    {
        $this->clearToken();
        Cache::forget('user_profile_' . $this->token);
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    // User Management Methods
    public function getUsers($filters = [])
    {
        $query = http_build_query($filters);
        $cacheKey = 'users_list_' . md5($query);

        return $this->makeRequest('GET', '/users?' . $query, null, true, $cacheKey, 60);
    }

    public function getUser($id)
    {
        return $this->makeRequest('GET', "/users/{$id}", null, true, "user_{$id}", 300);
    }

    public function createUser($userData)
    {
        $result = $this->makeRequest('POST', '/users', $userData);

        if ($result['success']) {
            Cache::forget('users_list_');
        }

        return $result;
    }

    public function updateUser($id, $userData)
    {
        $result = $this->makeRequest('PUT', "/users/{$id}", $userData);

        if ($result['success']) {
            Cache::forget("user_{$id}");
            Cache::forget('users_list_');
        }

        return $result;
    }

    public function deleteUser($id)
    {
        $result = $this->makeRequest('DELETE', "/users/{$id}");

        if ($result['success']) {
            Cache::forget("user_{$id}");
            Cache::forget('users_list_');
        }

        return $result;
    }

    // Task Management Methods
    public function getTasks($filters = [])
    {
        $query = http_build_query($filters);
        $cacheKey = 'tasks_list_' . md5($query);

        return $this->makeRequest('GET', '/tasks?' . $query, null, true, $cacheKey, 60);
    }

    public function getTask($id)
    {
        return $this->makeRequest('GET', "/tasks/{$id}", null, true, "task_{$id}", 300);
    }

    public function createTask($taskData)
    {
        $result = $this->makeRequest('POST', '/tasks', $taskData);

        if ($result['success']) {
            Cache::forget('tasks_list_');
        }

        return $result;
    }

    public function updateTask($id, $taskData)
    {
        $result = $this->makeRequest('PUT', "/tasks/{$id}", $taskData);

        if ($result['success']) {
            Cache::forget("task_{$id}");
            Cache::forget('tasks_list_');
        }

        return $result;
    }

    public function updateTaskStatus($id, $status)
    {
        $result = $this->makeRequest('PATCH', "/tasks/{$id}/status", ['status' => $status]);
        logger("updateTaskStatus: " . json_encode($result));

        if ($result['success']) {
            Cache::forget("task_{$id}");
            Cache::forget('tasks_list_');
        }

        return $result;
    }

    public function deleteTask($id)
    {
        $result = $this->makeRequest('DELETE', "/tasks/{$id}");

        if ($result['success']) {
            Cache::forget("task_{$id}");
            Cache::forget('tasks_list_');
        }

        return $result;
    }

    public function getUserTasks($userId)
    {
        return $this->makeRequest('GET', "/users/{$userId}/tasks", null, true, "user_tasks_{$userId}", 60);
    }

    // API Logs Methods
    public function getApiLogs($filters = [])
    {
        $query = http_build_query($filters);
        $cacheKey = 'logs_list_' . md5($query);
        return $this->makeRequest('GET', '/logs?' . $query, null, true, $cacheKey, 60);
    }

    public function getApiLog($id)
    {
        return $this->makeRequest('GET', "/logs/{$id}", null, true, "log_{$id}", 300);
    }
    /**
     * Clear all cache
     */
    public function clearCache()
    {
        Cache::flush();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return !empty($this->token);
    }
}

class TokenExpiredException extends \Exception {}
