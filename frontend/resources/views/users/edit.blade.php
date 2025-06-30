@extends('layouts.app')

@section('title', 'Edit User - NextWave Task Management')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit User
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user['id']) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user['name']) }}" 
                                       required 
                                       autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user['email']) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>New Password
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       minlength="8">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>Leave blank to keep current password
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status
                                </label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $user['is_active']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active User
                                    </label>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>Active users can log in to the system
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.show', $user['id']) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to User
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update User
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
    const form = document.querySelector('form');
    const passwordField = document.getElementById('password');
    
    form.addEventListener('submit', function(e) {
        const password = passwordField.value;
        
        if (password && password.length < 8) {
            e.preventDefault();
            passwordField.classList.add('is-invalid');
            passwordField.focus();
            return false;
        }
    });
    
    // Clear validation on input
    passwordField.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
@endpush 