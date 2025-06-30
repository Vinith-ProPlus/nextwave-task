<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        if (!$this->apiService->isAuthenticated()) {
            return redirect()->route('login');
        }

        // Get user profile
        $profile = $this->apiService->getProfile();
        
        // Get recent tasks
        $recentTasks = $this->apiService->getTasks(['per_page' => 5, 'sort_by' => 'created_at', 'sort_order' => 'desc']);
        
        // Get recent users
        $recentUsers = $this->apiService->getUsers(['per_page' => 5, 'sort_by' => 'created_at', 'sort_order' => 'desc']);

        // Calculate statistics
        $allTasks = $this->apiService->getTasks(['per_page' => 1000]);
        $allUsers = $this->apiService->getUsers(['per_page' => 1000]);

        $stats = [
            'total_tasks' => $allTasks['success'] ? count($allTasks['data']['data']) : 0,
            'total_users' => $allUsers['success'] ? count($allUsers['data']['data']) : 0,
            'pending_tasks' => 0,
            'completed_tasks' => 0,
        ];

        if ($allTasks['success']) {
            foreach ($allTasks['data']['data'] as $task) {
                if ($task['status'] === 'pending') {
                    $stats['pending_tasks']++;
                } elseif ($task['status'] === 'completed') {
                    $stats['completed_tasks']++;
                }
            }
        }

        return view('dashboard.index', compact('profile', 'recentTasks', 'recentUsers', 'stats'));
    }
}
