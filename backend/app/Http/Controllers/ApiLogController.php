<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Traits\ApiResponse;
use App\Traits\Filterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiLogController extends Controller
{
    use ApiResponse, Filterable;

    /**
     * Get all API logs with filtering, sorting, and pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate request parameters first
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'method' => 'nullable|in:GET,POST,PUT,PATCH,DELETE',
                'status_code' => 'nullable|integer|min:100|max:599',
                'status_code_range' => 'nullable|in:1xx,2xx,3xx,4xx,5xx',
                'min_duration' => 'nullable|numeric|min:0',
                'max_duration' => 'nullable|numeric|min:0',
                'created_at_from' => 'nullable|date',
                'created_at_to' => 'nullable|date|after_or_equal:created_at_from',
                'sort_by' => 'nullable|in:method,status_code,duration_ms,timestamp',
                'sort_order' => 'nullable|in:asc,desc',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1',
            ]);

            // Add custom validation for duration range
            $validator->after(function ($validator) use ($request) {
                if ($request->filled('min_duration') && $request->filled('max_duration')) {
                    if ($request->min_duration > $request->max_duration) {
                        $validator->errors()->add('max_duration', 'The max duration must be greater than min duration.');
                    }
                }
            });

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = ApiLog::query();

            // Define filters configuration
            $filters = [
                'method' => ['type' => 'exact'],
                'status_code' => ['type' => 'exact'],
                'status_code_range' => ['type' => 'status_code_range'],
                'created_at_from' => ['type' => 'date_range', 'start_field' => 'timestamp', 'operator' => '>='],
                'created_at_to' => ['type' => 'date_range', 'end_field' => 'timestamp', 'operator' => '<='],
            ];

            $searchableFields = ['endpoint', 'ip', 'user_agent'];
            $sortableFields = ['method', 'status_code', 'duration_ms', 'timestamp'];

            // Apply filters, sorting, and pagination
            $result = $this->applyFilters(
                $query,
                $request,
                $filters,
                $searchableFields,
                $sortableFields,
                'timestamp',
                'desc',
                15
            );

            if (!$result['success']) {
                return $this->validationErrorResponse($result['errors']);
            }

            return $this->successResponse($result['data'], 'API logs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve API logs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific API log
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $log = ApiLog::find($id);

            if (!$log) {
                return $this->notFoundResponse('API log not found');
            }

            return $this->successResponse($log, 'API log retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve API log: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get API logs statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_requests' => ApiLog::count(),
                'requests_by_method' => ApiLog::selectRaw('method, COUNT(*) as count')
                    ->groupBy('method')
                    ->get(),
                'requests_by_status' => ApiLog::selectRaw('status_code, COUNT(*) as count')
                    ->groupBy('status_code')
                    ->orderBy('status_code')
                    ->get(),
                'average_response_time' => ApiLog::avg('duration_ms'),
                'requests_today' => ApiLog::whereDate('timestamp', Carbon::today())->count(),
                'requests_this_week' => ApiLog::whereBetween('timestamp', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
                'requests_this_month' => ApiLog::whereMonth('timestamp', Carbon::now()->month)->count(),
                'methods' => ApiLog::selectRaw('method, COUNT(*) as count')
                    ->groupBy('method')
                    ->orderBy('count', 'desc')
                    ->get(),
                'status_codes' => ApiLog::selectRaw('status_code, COUNT(*) as count')
                    ->groupBy('status_code')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'top_endpoints' => ApiLog::selectRaw('endpoint, COUNT(*) as count')
                    ->groupBy('endpoint')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return $this->successResponse($stats, 'API statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve API statistics: ' . $e->getMessage(), 500);
        }
    }
}
