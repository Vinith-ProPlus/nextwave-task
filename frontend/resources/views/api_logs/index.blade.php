@extends('layouts.app')

@section('title', 'API Logs - NextWave Task Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-list-alt me-2 text-primary"></i>API Logs
                    </h2>
                    <p class="text-muted mb-0">Monitor and analyze API request logs</p>
                </div>
            </div>
        </div>
    </div>



    <!-- API Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>API Logs List
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="apiLogsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Method</th>
                                    <th>Endpoint</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#apiLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("api_logs.index") }}',
                type: 'GET',
                data: function(d) {
                    // Add any additional parameters if needed
                }
            },
            columns: [
                { data: 0, name: 'timestamp', orderable: true, searchable: false },
                { data: 1, name: 'method', orderable: true, searchable: true },
                { data: 2, name: 'endpoint', orderable: false, searchable: true },
                { data: 3, name: 'status_code', orderable: true, searchable: true },
                { data: 4, name: 'duration_ms', orderable: true, searchable: false },
                { data: 5, name: 'ip', orderable: false, searchable: true },
                { data: 6, name: 'timestamp', orderable: true, searchable: false },
                { data: 7, name: 'actions', orderable: false, searchable: false }
            ],
            responsive: true,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                search: '<i class="fas fa-search"></i>',
                searchPlaceholder: 'Search logs...',
                lengthMenu: 'Show _MENU_ logs per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ logs',
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
            pageLength: 25,
            order: [[6, 'desc']], // Sort by timestamp column (index 6) descending
            columnDefs: [
                { orderable: false, targets: 7 }
            ]
        });


    });
</script>
@endpush
