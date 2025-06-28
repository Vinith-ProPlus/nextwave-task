@extends('layouts.app')

@section('title', 'Dashboard - NextWave Task Manager')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-white fade-in">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h1>
            <p class="text-white-50">Welcome back! Here's what's happening with your tasks.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 mb-3"></i>
                    <h3 class="fw-bold" id="totalUsers">{{ $stats['total_users'] }}</h3>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-person-check display-4 mb-3"></i>
                    <h3 class="fw-bold" id="activeUsers">{{ $stats['active_users'] }}</h3>
                    <p class="mb-0">Active Users</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-list-task display-4 mb-3"></i>
                    <h3 class="fw-bold" id="totalTasks">{{ $stats['total_tasks'] }}</h3>
                    <p class="mb-0">Total Tasks</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card fade-in">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-3"></i>
                    <h3 class="fw-bold" id="completedTasks">{{ $stats['completed_tasks'] }}</h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card fade-in">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-clock text-warning"></i> Pending Tasks
                    </h5>
                    <h2 class="text-warning fw-bold" id="pendingTasks">{{ $stats['pending_tasks'] }}</h2>
                    <p class="text-muted">Tasks waiting to be started</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card fade-in">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-triangle text-danger"></i> Overdue Tasks
                    </h5>
                    <h2 class="text-danger fw-bold" id="overdueTasks">{{ $stats['overdue_tasks'] }}</h2>
                    <p class="text-muted">Tasks past their due date</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card fade-in">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('users.index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('tasks.index') }}" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Manage Tasks
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-info w-100" onclick="refreshStats()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshStats() {
    showLoading();

    // Animate counters while fetching new data
    animateCounters();

    setTimeout(() => {
        hideLoading();
        showAlert('success', 'Refreshed!', 'Dashboard stats have been updated');
    }, 1000);
}

function animateCounters() {
    const counters = ['#totalUsers', '#activeUsers', '#totalTasks', '#completedTasks', '#pendingTasks', '#overdueTasks'];

    counters.forEach(selector => {
        const element = $(selector);
        const finalValue = parseInt(element.text());

        gsap.fromTo(element,
            { textContent: 0 },
            {
                textContent: finalValue,
                duration: 2,
                ease: "power2.out",
                snap: { textContent: 1 },
                onUpdate: function() {
                    element.text(Math.ceil(this.targets()[0].textContent));
                }
            }
        );
    });
}

// Animate stats on page load
$(document).ready(function() {
    setTimeout(animateCounters, 500);
});
</script>
@endsection
