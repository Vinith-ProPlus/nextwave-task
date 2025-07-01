@extends('layouts.app')

@section('title', 'API Log Details - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-list-alt me-2 text-primary"></i>API Log Details
                    </h2>
                    <p class="text-muted mb-0">Detailed information about API request #{{ $log['id'] }}</p>
                </div>
                <a href="{{ route('api_logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Logs
                </a>
            </div>
        </div>
    </div>

    <!-- API Log Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Request Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Request ID:</th>
                                    <td><strong>{{ $log['id'] }}</strong></td>
                                </tr>
                                <tr>
                                    <th>HTTP Method:</th>
                                    <td>
                                        @php
                                            $methodColors = [
                                                'GET' => 'success',
                                                'POST' => 'primary',
                                                'PUT' => 'warning',
                                                'PATCH' => 'info',
                                                'DELETE' => 'danger'
                                            ];
                                            $color = $methodColors[$log['method']] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }} fs-6">
                                            {{ $log['method'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Endpoint:</th>
                                    <td>
                                        <code class="text-break">{{ $log['endpoint'] }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Code:</th>
                                    <td>
                                        @php
                                            $statusColor = 'secondary';
                                            if ($log['status_code'] >= 200 && $log['status_code'] < 300) $statusColor = 'success';
                                            elseif ($log['status_code'] >= 300 && $log['status_code'] < 400) $statusColor = 'info';
                                            elseif ($log['status_code'] >= 400 && $log['status_code'] < 500) $statusColor = 'warning';
                                            elseif ($log['status_code'] >= 500) $statusColor = 'danger';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }} fs-6">
                                            {{ $log['status_code'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Response Time:</th>
                                    <td>
                                        <span class="badge bg-{{ $log['duration_ms'] > 1000 ? 'danger' : ($log['duration_ms'] > 500 ? 'warning' : 'success') }} fs-6">
                                            {{ number_format($log['duration_ms'], 2) }}ms
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP Address:</th>
                                    <td><code>{{ $log['ip'] }}</code></td>
                                </tr>
                                <tr>
                                    <th>User Agent:</th>
                                    <td>
                                        <small class="text-muted text-break">{{ $log['user_agent'] }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Timestamp:</th>
                                    <td>
                                        <span title="{{ \Carbon\Carbon::parse($log['timestamp'])->format('F d, Y \a\t g:i:s A') }}">
                                            {{ \Carbon\Carbon::parse($log['timestamp'])->format('F d, Y \a\t g:i:s A') }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Analysis -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Performance Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-6 text-{{ $log['duration_ms'] > 1000 ? 'danger' : ($log['duration_ms'] > 500 ? 'warning' : 'success') }}">
                                    {{ number_format($log['duration_ms'], 2) }}ms
                                </div>
                                <p class="text-muted">Response Time</p>
                                @if($log['duration_ms'] > 1000)
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Slow response time detected
                                    </div>
                                @elseif($log['duration_ms'] > 500)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        Moderate response time
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Good response time
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-6 text-{{ $log['status_code'] >= 200 && $log['status_code'] < 300 ? 'success' : ($log['status_code'] >= 400 ? 'danger' : 'warning') }}">
                                    {{ $log['status_code'] }}
                                </div>
                                <p class="text-muted">HTTP Status</p>
                                @if($log['status_code'] >= 200 && $log['status_code'] < 300)
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Successful request
                                    </div>
                                @elseif($log['status_code'] >= 400)
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Client or server error
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informational or redirect
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-6 text-primary">
                                    {{ $log['method'] }}
                                </div>
                                <p class="text-muted">HTTP Method</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    @switch($log['method'])
                                        @case('GET')
                                            Read operation
                                            @break
                                        @case('POST')
                                            Create operation
                                            @break
                                        @case('PUT')
                                            Update operation (full)
                                            @break
                                        @case('PATCH')
                                            Update operation (partial)
                                            @break
                                        @case('DELETE')
                                            Delete operation
                                            @break
                                        @default
                                            Other operation
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
