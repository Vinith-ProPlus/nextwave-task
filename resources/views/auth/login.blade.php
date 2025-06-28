@extends('layouts.app')

@section('title', 'Login - NextWave Task Manager')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-4 mx-auto">
            <div class="card fade-in">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-layers text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 fw-bold text-primary">NextWave Task</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <span class="btn-text">Sign In</span>
                            <span class="loading spinner-border spinner-border-sm ms-2" role="status"></span>
                        </button>
                    </form>

                    <div class="text-center">
                        <small class="text-muted">Demo Credentials: admin@test.com / password</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check if already logged in
    if (localStorage.getItem('auth_token')) {
        window.location.href = '/dashboard';
    }

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            email: $('#email').val(),
            password: $('#password').val()
        };

        // Reset previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Show loading
        $('.btn-text').text('Signing In...');
        $('.loading').addClass('show');

        $.ajax({
            url: '/api/login',
            method: 'POST',
            data: JSON.stringify(formData),
            success: function(response) {
                localStorage.setItem('auth_token', response.data.token);
                localStorage.setItem('user_data', JSON.stringify(response.data.user));

                showAlert('success', 'Welcome!', 'Login successful');

                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1500);
            },
            error: function(xhr) {
                hideLoading();
                $('.btn-text').text('Sign In');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}`).siblings('.invalid-feedback').text(errors[key][0]);
                    });
                } else {
                    showAlert('error', 'Login Failed',
                        xhr.responseJSON?.message || 'Please check your credentials');
                }
            }
        });
    });
});
</script>
@endsection
