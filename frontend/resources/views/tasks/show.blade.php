@extends('layouts.app')

@section('title', 'Task Details - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-tasks me-2 text-primary"></i>Task Details
                    </h2>
                    <p class="text-muted mb-0">View task information and details</p>
                </div>
                <div>
                    <a href="{{ route('tasks.edit', $task['id']) }}" class="btn btn-primary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('tasks.destroy', $task['id']) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <a href="javascript:window.history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Information -->
        <div class="col-md-12">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Task Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h3 class="mb-2">{{ $task['title'] }}</h3>
                            @if($task['description'])
                                <p class="text-muted mb-0">{{ $task['description'] }}</p>
                            @else
                                <p class="text-muted mb-0"><em>No description provided</em></p>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tasks me-1"></i>Status
                            </label>
                            <div>
                                @switch($task['status'])
                                    @case('pending')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                        @break
                                    @case('in_progress')
                                        <span class="badge bg-info">
                                            <i class="fas fa-spinner me-1"></i>In Progress
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Completed
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times-circle me-1"></i>Cancelled
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ ucfirst($task['status']) }}</span>
                                @endswitch
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-exclamation-triangle me-1"></i>Priority
                            </label>
                            <div>
                                @switch($task['priority'])
                                    @case('low')
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-down me-1"></i>Low
                                        </span>
                                        @break
                                    @case('medium')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-minus me-1"></i>Medium
                                        </span>
                                        @break
                                    @case('high')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-arrow-up me-1"></i>High
                                        </span>
                                        @break
                                    @case('urgent')
                                        <span class="badge bg-dark">
                                            <i class="fas fa-exclamation me-1"></i>Urgent
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ ucfirst($task['priority']) }}</span>
                                @endswitch
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar me-1"></i>Due Date
                            </label>
                            <p class="mb-0">
                                @if($task['due_date'])
                                    <span title="{{ \Carbon\Carbon::parse($task['due_date'])->format('F d, Y \a\t g:i A') }}">
                                        {{ \Carbon\Carbon::parse($task['due_date'])->format('F d, Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        @if(\Carbon\Carbon::parse($task['due_date'])->isPast())
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                            </span>
                                        @elseif(\Carbon\Carbon::parse($task['due_date'])->isToday())
                                            <span class="text-warning">
                                                <i class="fas fa-clock me-1"></i>Due today
                                            </span>
                                        @else
                                            <span class="text-info">
                                                <i class="fas fa-calendar-day me-1"></i>{{ \Carbon\Carbon::parse($task['due_date'])->diffForHumans() }}
                                            </span>
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">No due date set</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user me-1"></i>Assigned To
                            </label>
                            <div class="mb-0">
                                @if(isset($task['user']) && $task['user'])
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <span class="text-white fw-bold">{{ strtoupper(substr($task['user']['name'], 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $task['user']['name'] }}</span>
                                            <br>
                                            <small class="text-muted">{{ $task['user']['email'] }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user-plus me-1"></i>Assigned By
                            </label>
                            <div class="mb-0">
                                @if(isset($task['assigned_by']) && $task['assigned_by'])
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <span class="text-white fw-bold">{{ strtoupper(substr($task['assigned_by']['name'], 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $task['assigned_by']['name'] }}</span>
                                            <br>
                                            <small class="text-muted">{{ $task['assigned_by']['email'] }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-plus me-1"></i>Created
                            </label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($task['created_at'])->format('F d, Y \a\t g:i A') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($task['created_at'])->diffForHumans() }}
                                </small>
                            </p>
                        </div>

                        @if(isset($task['completed_at']) && $task['completed_at'])
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-check-double me-1"></i>Completed
                            </label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($task['completed_at'])->format('F d, Y \a\t g:i A') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($task['completed_at'])->diffForHumans() }}
                                </small>
                            </p>
                        </div>
                        @endif

                        @if(isset($task['updated_at']) && $task['updated_at'] != $task['created_at'])
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-edit me-1"></i>Last Updated
                            </label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($task['updated_at'])->format('F d, Y \a\t g:i A') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($task['updated_at'])->diffForHumans() }}
                                </small>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any() && !request()->has('status-updated'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
