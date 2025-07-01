@extends('layouts.auth')

@section('title', 'Login - NextWave Task Management')

@section('content')
<div class="card fade-in">
    <div class="card-header">
        <h3 class="mb-0">
            <i class="fas fa-rocket me-2"></i>NextWave
        </h3>
        <p class="mb-0 text-white-50">Welcome back!</p>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-1"></i>Email Address
                </label>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-1"></i>Password
                </label>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="mb-0">Don't have an account?
                <a href="{{ route('register') }}" class="text-decoration-none">
                    Register here
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .main-container {
        background: transparent;
        box-shadow: none;
        margin: 0;
        min-height: auto;
    }

    .card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-header {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 15px 15px 0 0 !important;
        border: none;
        padding: 2rem 1.5rem 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5b21b6, #7c3aed);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }
</style>
@endpush
