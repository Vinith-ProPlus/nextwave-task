@extends('layouts.app')

@section('title', 'Dashboard - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-6 mb-2">
                        <i class="fas fa-rocket me-2 text-primary"></i>
                        Welcome to NextWave Task Management
                    </h1>
                    <p class="lead text-muted">
                        Manage your tasks and users efficiently with our powerful platform
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-primary bg-gradient rounded-circle p-3 me-3">
                            <i class="fas fa-tasks text-white fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold text-primary">{{ $stats['total_tasks'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">Total Tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-success bg-gradient rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle text-white fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold text-success">{{ $stats['completed_tasks'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-warning bg-gradient rounded-circle p-3 me-3">
                            <i class="fas fa-clock text-white fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold text-warning">{{ $stats['pending_tasks'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-info bg-gradient rounded-circle p-3 me-3">
                            <i class="fas fa-users text-white fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold text-info">{{ $stats['total_users'] ?? 0 }}</h2>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Recent Tasks
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recentTasks['success']) && $recentTasks['success'] && count($recentTasks['data']['data']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTasks['data']['data'] as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tasks.show', $task['id']) }}" class="text-decoration-none">
                                                {{ Str::limit($task['title'], 30) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge status-{{ $task['status'] }}">
                                                {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge priority-{{ $task['priority'] }}">
                                                {{ ucfirst($task['priority']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task['due_date'])
                                                {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-sm">
                                View All Tasks
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent tasks found</p>
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                                Create First Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Recent Users
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recentUsers['success']) && $recentUsers['success'] && count($recentUsers['data']['data']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers['data']['data'] as $user)
                                    <tr>
                                        <td>
                                            <a href="{{ route('users.show', $user['id']) }}" class="text-decoration-none">
                                                {{ $user['name'] }}
                                            </a>
                                        </td>
                                        <td>{{ $user['email'] }}</td>
                                        <td>
                                            @if($user['is_active'])
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($user['created_at'])->format('M d, Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">
                                View All Users
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent users found</p>
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                Create First User
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>Create Task
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-user-plus me-2"></i>Add User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('tasks.index') }}" class="btn btn-info w-100">
                                <i class="fas fa-list me-2"></i>View Tasks
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('users.index') }}" class="btn btn-warning w-100">
                                <i class="fas fa-users me-2"></i>View Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .status-pending { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .status-in_progress { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .status-completed { background: linear-gradient(135deg, #10b981, #059669); }
    .status-cancelled { background: linear-gradient(135deg, #ef4444, #dc2626); }

    .priority-low { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .priority-medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .priority-high { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .priority-urgent { background: linear-gradient(135deg, #7c3aed, #5b21b6); }
</style>
@endpush
