@extends('layouts.app')

@section('title', 'Edit Task - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-edit me-2 text-primary"></i>Edit Task
                    </h2>
                    <p class="text-muted mb-0">Update task information and details</p>
                </div>
                <a href="{{ route('tasks.show', $task['id']) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Task
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Task Form -->
    <div class="row">
        <div class="col-12">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Task Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task['id']) }}" id="editTaskForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Title -->
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Task Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title', $task['title']) }}" 
                                           placeholder="Enter task title"
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Priority <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority" 
                                            required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority', $task['priority']) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $task['priority']) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $task['priority']) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $task['priority']) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Enter task description">{{ old('description', $task['description']) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Assigned User -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>Assigned To
                                    </label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" 
                                            name="user_id">
                                        <option value="">Unassigned</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user['id'] }}" 
                                                    {{ old('user_id', $task['user_id']) == $user['id'] ? 'selected' : '' }}>
                                                {{ $user['name'] }} ({{ $user['email'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Due Date
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="{{ old('due_date', $task['due_date'] ? \Carbon\Carbon::parse($task['due_date'])->format('Y-m-d\TH:i') : '') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-tasks me-1"></i>Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ old('status', $task['status']) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ old('status', $task['status']) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status', $task['status']) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $task['status']) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tasks.show', $task['id']) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('editTaskForm');
    const titleField = document.getElementById('title');
    const priorityField = document.getElementById('priority');
    const statusField = document.getElementById('status');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous validation
        [titleField, priorityField, statusField].forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        // Validate title
        if (!titleField.value.trim()) {
            titleField.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate priority
        if (!priorityField.value) {
            priorityField.classList.add('is-invalid');
            isValid = false;
        }
        
        // Validate status
        if (!statusField.value) {
            statusField.classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
    });
    
    // Clear validation on input
    [titleField, priorityField, statusField].forEach(field => {
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
    });
});
</script>
@endpush 