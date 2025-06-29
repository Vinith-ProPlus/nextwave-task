<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;

class ApiLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);

        try {
            ApiLog::create([
                'method' => $request->method(),
                'endpoint' => $request->fullUrl(),
                'timestamp' => date('Y-m-d H:i:s'),
                'duration_ms' => $duration,
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database logging fails
            Log::info('API Request', [
                'method' => $request->method(),
                'endpoint' => $request->fullUrl(),
                'timestamp' => date('Y-m-d H:i:s'),
                'duration_ms' => $duration,
                'status_code' => $response->getStatusCode(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
