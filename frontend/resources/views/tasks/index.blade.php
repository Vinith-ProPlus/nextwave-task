@extends('layouts.app')

@section('title', 'Tasks - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-tasks me-2 text-primary"></i>Tasks Management
                    </h2>
                    <p class="text-muted mb-0">Manage and track all tasks in the system</p>
                </div>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Task
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('tasks.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search tasks...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">All Priority</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                                <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Title</option>
                                <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                                <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>Priority</option>
                                <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_order" class="form-label">Order</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Tasks List
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($tasks) && count($tasks) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="tasksTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Due Date</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ $task['id'] }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ Str::limit($task['title'], 40) }}</strong>
                                                @if($task['description'])
                                                    <br><small class="text-muted">{{ Str::limit($task['description'], 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($task['user']) && $task['user'])
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                        <span class="text-white fw-bold small">{{ strtoupper(substr($task['user']['name'], 0, 1)) }}</span>
                                                    </div>
                                                    <span>{{ $task['user']['name'] }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
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
                                                @php
                                                    $dueDate = \Carbon\Carbon::parse($task['due_date']);
                                                    $isOverdue = $dueDate->isPast() && $task['status'] !== 'completed';
                                                @endphp
                                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}" title="{{ $dueDate->format('F d, Y \a\t g:i A') }}">
                                                    {{ $dueDate->format('M d, Y') }}
                                                    @if($isOverdue)
                                                        <i class="fas fa-exclamation-triangle ms-1"></i>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span title="{{ \Carbon\Carbon::parse($task['created_at'])->format('F d, Y \a\t g:i A') }}">
                                                {{ \Carbon\Carbon::parse($task['created_at'])->diffForHumans() }}
                                            </span>
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
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="confirmDelete('{{ route('tasks.destroy', $task['id']) }}', 'Delete Task', 'Are you sure you want to delete this task? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($pagination))
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Showing {{ $pagination['from'] ?? 0 }} to {{ $pagination['to'] ?? 0 }} of {{ $pagination['total'] ?? 0 }} entries
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    @if($pagination['current_page'] > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                                        <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    @if($pagination['current_page'] < $pagination['last_page'])
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tasks found</h5>
                            <p class="text-muted">Get started by creating your first task.</p>
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Task
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#tasksTable').DataTable({
            responsive: true,
            language: {
                search: '<i class="fas fa-search"></i>',
                searchPlaceholder: 'Search tasks...',
                lengthMenu: 'Show _MENU_ tasks per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ tasks',
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>'
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: 7 }
            ]
        });
    });
</script>
@endpush
