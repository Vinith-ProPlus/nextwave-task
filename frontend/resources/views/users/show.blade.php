@extends('layouts.app')

@section('title', 'User Details - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-user me-2 text-primary"></i>User Details
                    </h2>
                    <p class="text-muted mb-0">View user information and assigned tasks</p>
                </div>
                <div>
                    <a href="{{ route('users.edit', $user['id']) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>
                    <a href="javascript:window.history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder mb-3">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>
                        <h4 class="mb-1">{{ $user['name'] }}</h4>
                        <p class="text-muted mb-0">{{ $user['email'] }}</p>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-id-badge me-1"></i>User ID
                            </label>
                            <p class="mb-0">{{ $user['id'] }}</p>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on me-1"></i>Status
                            </label>
                            <div>
                                @if($user['is_active'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-plus me-1"></i>Created
                            </label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($user['created_at'])->format('F d, Y \a\t g:i A') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($user['created_at'])->diffForHumans() }}
                                </small>
                            </p>
                        </div>

                        @if(isset($user['updated_at']) && $user['updated_at'] != $user['created_at'])
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-edit me-1"></i>Last Updated
                            </label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($user['updated_at'])->format('F d, Y \a\t g:i A') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($user['updated_at'])->diffForHumans() }}
                                </small>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- User Tasks -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Assigned Tasks
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($tasks) && count($tasks) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tasks.show', $task['id']) }}" class="text-decoration-none">
                                                {{ $task['title'] }}
                                            </a>
                                        </td>
                                        <td>
                                            @switch($task['status'])
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('in_progress')
                                                    <span class="badge bg-info">In Progress</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-secondary">Cancelled</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($task['status']) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($task['priority'])
                                                @case('low')
                                                    <span class="badge bg-success">Low</span>
                                                    @break
                                                @case('medium')
                                                    <span class="badge bg-warning">Medium</span>
                                                    @break
                                                @case('high')
                                                    <span class="badge bg-danger">High</span>
                                                    @break
                                                @case('urgent')
                                                    <span class="badge bg-dark">Urgent</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($task['priority']) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($task['due_date'])
                                                <span title="{{ \Carbon\Carbon::parse($task['due_date'])->format('F d, Y \a\t g:i A') }}">
                                                    {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tasks.show', $task['id']) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('tasks.edit', $task['id']) }}"
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tasks assigned</h5>
                            <p class="text-muted">This user hasn't been assigned any tasks yet.</p>
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Assign Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 3px solid #dee2e6;
}
</style>
@endpush
