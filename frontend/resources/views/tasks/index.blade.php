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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Tasks List
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tasksTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Assigned To</th>
                                    <th>Assigned By</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
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
        $('#tasksTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('tasks.index') }}',
                type: 'GET'
            },
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
                { orderable: false, targets: 8 }
            ]
        });
    });
</script>
@endpush
