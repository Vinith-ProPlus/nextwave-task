<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log API requests asynchronously
        try {
            $this->logRequest($request, $response);
        } catch (\Exception $e) {
            Log::error('Failed to log API request: ' . $e->getMessage());
        }

        return $response;
    }

    private function logRequest(Request $request, $response): void
    {
        $requestData = $request->all();

        // Remove sensitive data from logging
        $sensitiveFields = ['password', 'password_confirmation', 'token'];
        foreach ($sensitiveFields as $field) {
            if (isset($requestData[$field])) {
                $requestData[$field] = '[HIDDEN]';
            }
        }

        ApiLog::create([
            'method' => $request->method(),
            'endpoint' => $request->getPathInfo(),
            'request_data' => !empty($requestData) ? $requestData : null,
            'response_status' => $response->getStatusCode(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
