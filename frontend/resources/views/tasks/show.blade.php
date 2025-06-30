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
                    <a href="{{ route('tasks.edit', $task['id']) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Edit Task
                    </a>
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Information -->
        <div class="col-md-8">
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

        <!-- Task Assignment & Actions -->
        <div class="col-md-4">
            <div class="card slide-in-right">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Assignment
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($task['user']) && $task['user'])
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-gradient rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fw-bold fs-4">{{ strtoupper(substr($task['user']['name'], 0, 1)) }}</span>
                            </div>
                            <h5 class="mb-1">{{ $task['user']['name'] }}</h5>
                            <p class="text-muted mb-0">{{ $task['user']['email'] }}</p>
                            <a href="{{ route('users.show', $task['user']['id']) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-eye me-1"></i>View Profile
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Unassigned</h5>
                            <p class="text-muted">This task is not assigned to anyone.</p>
                            <a href="{{ route('tasks.edit', $task['id']) }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>Assign Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task['status'] !== 'completed')
                            <button type="button" 
                                    class="btn btn-success" 
                                    onclick="updateTaskStatus('{{ route('tasks.update-status', $task['id']) }}', 'completed')">
                                <i class="fas fa-check me-1"></i>Mark as Completed
                            </button>
                        @endif
                        
                        @if($task['status'] === 'pending')
                            <button type="button" 
                                    class="btn btn-info" 
                                    onclick="updateTaskStatus('{{ route('tasks.update-status', $task['id']) }}', 'in_progress')">
                                <i class="fas fa-play me-1"></i>Start Task
                            </button>
                        @endif
                        
                        @if($task['status'] === 'in_progress')
                            <button type="button" 
                                    class="btn btn-warning" 
                                    onclick="updateTaskStatus('{{ route('tasks.update-status', $task['id']) }}', 'pending')">
                                <i class="fas fa-pause me-1"></i>Pause Task
                            </button>
                        @endif
                        
                        @if($task['status'] !== 'cancelled')
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    onclick="updateTaskStatus('{{ route('tasks.update-status', $task['id']) }}', 'cancelled')">
                                <i class="fas fa-ban me-1"></i>Cancel Task
                            </button>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-danger" 
                                onclick="confirmDelete('{{ route('tasks.destroy', $task['id']) }}', 'Delete Task', 'Are you sure you want to delete this task? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i>Delete Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateTaskStatus(url, status) {
    const statusLabels = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
    };
    
    Swal.fire({
        title: 'Update Task Status',
        text: `Are you sure you want to mark this task as "${statusLabels[status]}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            $.ajax({
                url: url,
                type: 'PATCH',
                data: { status: status },
                success: function(response) {
                    hideLoading();
                    Toast.fire({
                        icon: 'success',
                        title: 'Task status updated successfully!'
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    hideLoading();
                    Toast.fire({
                        icon: 'error',
                        title: 'Error updating task status'
                    });
                }
            });
        }
    });
}
</script>
@endpush 