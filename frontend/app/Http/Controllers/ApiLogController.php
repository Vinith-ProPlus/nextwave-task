<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ApiService;
use App\Services\TokenExpiredException;

class ApiLogController extends Controller
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

            // Check if this is a DataTables AJAX request
            if ($request->ajax() && $request->has('draw')) {
                return $this->handleDataTablesRequest($request);
            }

            // Regular page load - show the view
            return view('api_logs.index');
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }

    private function handleDataTablesRequest(Request $request)
    {
        try {
            // Extract DataTables parameters
            $draw = $request->input('draw');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $search = $request->input('search.value', '');
            $order = $request->input('order.0', []);

            // Build filters for API
            $filters = [
                'page' => ($start / $length) + 1,
                'per_page' => $length,
                'search' => $search,
            ];

            // Handle column-specific search
            $columns = $request->input('columns', []);
            foreach ($columns as $index => $column) {
                if (!empty($column['search']['value'])) {
                    switch ($index) {
                        case 1: // Method column
                            $filters['method'] = $column['search']['value'];
                            break;
                        case 3: // Status code column
                            $filters['status_code'] = $column['search']['value'];
                            break;
                    }
                }
            }

                        // Handle sorting
            if (!empty($order)) {
                $columnIndex = $order['column'];
                $columnDirection = $order['dir'];

                $sortMap = [
                    0 => 'timestamp', // ID column maps to timestamp (most recent first)
                    1 => 'method',
                    3 => 'status_code',
                    4 => 'duration_ms',
                    6 => 'timestamp'
                ];

                if (isset($sortMap[$columnIndex])) {
                    $filters['sort_by'] = $sortMap[$columnIndex];
                    $filters['sort_order'] = $columnDirection;
                }
            }

            // Validate and clean filters before sending to API
            $validSortFields = ['timestamp', 'method', 'status_code', 'duration_ms'];
            if (isset($filters['sort_by']) && !in_array($filters['sort_by'], $validSortFields)) {
                $filters['sort_by'] = 'timestamp'; // Default to timestamp if invalid
            }

            // Get data from API
            Log::info('DataTables API Logs Request', [
                'filters' => $filters,
                'draw' => $draw
            ]);

            $result = $this->apiService->getApiLogs($filters);

            if (!$result['success']) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $result['message']
                ]);
            }

            $data = $result['data']['data'];
            $pagination = $result['data']['pagination'];

            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $log) {
                $formattedData[] = [
                    $log['id'],
                    '<span class="badge bg-' . $this->getMethodColor($log['method']) . '">' . $log['method'] . '</span>',
                    '<div class="text-truncate" style="max-width: 300px;" title="' . $log['endpoint'] . '">' . $log['endpoint'] . '</div>',
                    '<span class="badge bg-' . $this->getStatusColor($log['status_code']) . '">' . $log['status_code'] . '</span>',
                    '<span class="badge bg-' . $this->getDurationColor($log['duration_ms']) . '">' . number_format($log['duration_ms'], 2) . 'ms</span>',
                    '<code>' . $log['ip'] . '</code>',
                    '<span>' . \Carbon\Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s') . '</span>',
                    '<div class="btn-group" role="group">
                        <a href="' . route('api_logs.show', $log['id']) . '" class="btn btn-sm btn-outline-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>'
                ];
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $pagination['total'],
                'recordsFiltered' => $pagination['total'],
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            Log::error('DataTables API Logs Error: ' . $e->getMessage(), [
                'draw' => $draw ?? 1,
                'filters' => $filters ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while fetching data: ' . $e->getMessage()
            ]);
        }
    }

    private function getMethodColor($method)
    {
        $colors = [
            'GET' => 'success',
            'POST' => 'primary',
            'PUT' => 'warning',
            'PATCH' => 'info',
            'DELETE' => 'danger'
        ];
        return $colors[$method] ?? 'secondary';
    }

    private function getStatusColor($statusCode)
    {
        if ($statusCode >= 200 && $statusCode < 300) return 'success';
        if ($statusCode >= 300 && $statusCode < 400) return 'info';
        if ($statusCode >= 400 && $statusCode < 500) return 'warning';
        if ($statusCode >= 500) return 'danger';
        return 'secondary';
    }

    private function getDurationColor($duration)
    {
        if ($duration > 1000) return 'danger';
        if ($duration > 500) return 'warning';
        return 'success';
    }

    public function show($id)
    {
        try {
            if (!$this->apiService->isAuthenticated()) {
                return redirect()->route('login');
            }

            $result = $this->apiService->getApiLog($id);
            if (!$result['success']) {
                return redirect()->route('api_logs.index')
                    ->with('error', $result['message'] ?? 'API log not found');
            }

            return view('api_logs.show', ['log' => $result['data']]);
        } catch (TokenExpiredException $e) {
            return redirect()->route('login')->with('error', 'Session expired, please log in again.');
        }
    }
}
