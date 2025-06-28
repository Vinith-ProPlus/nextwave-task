<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'total_tasks' => Task::count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'pending_tasks' => Task::where('status', 'pending')->count(),
                'overdue_tasks' => Task::overdue()->count(),
            ];
        });

        return view('dashboard.index', compact('stats'));
    }

    public function users()
    {
        return view('dashboard.users');
    }

    public function tasks()
    {
        return view('dashboard.tasks');
    }

    public function login()
    {
        return view('auth.login');
    }
}
