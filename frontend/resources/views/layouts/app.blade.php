<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NextWave Task Management')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #111;
            min-height: 100vh;
        }
        .main-container {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin: 20px auto;
            min-height: calc(100vh - 40px);
            max-width: 1200px;
            color: #111;
        }
        .navbar {
            background: #222;
            color: #fff;
            border-radius: 8px 8px 0 0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
        }
        .navbar-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .nav-link {
            color: #fff !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
        }
        .nav-link.active, .nav-link:hover {
            background: #444;
        }
        .logout-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            margin-left: 1rem;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #b52a37;
        }
        .content-wrapper {
            padding: 2rem;
        }
        .card {
            background: #fff;
            border: 1px solid #bbb;
            border-radius: 8px;
            box-shadow: none;
            margin-bottom: 1.5rem;
            color: #111;
        }
        .card-header {
            background: #eee;
            color: #111;
            border-radius: 8px 8px 0 0;
            border-bottom: 1px solid #bbb;
            padding: 1rem;
        }
        .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border: 1px solid #bbb;
            background: #f4f4f4;
            color: #111;
        }
        .btn-primary { background: #007bff; color: #fff; border-color: #007bff; }
        .btn-secondary { background: #6c757d; color: #fff; border-color: #6c757d; }
        .btn:hover {
            /* opacity: 0.9; */
        }
        .table { background: #fff; border: 1px solid #bbb; }
        .table th { background: #eee; color: #111; }
        .badge { background: #eee; color: #111; border-radius: 4px; padding: 0.25em 0.5em; }
        .alert { border-radius: 6px; }
    </style>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @stack('styles')
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <div class="main-container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="/">{{ env('APP_NAME', 'Project Name') }}</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('users.index') }}">Manage Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tasks.index') }}">Manage Tasks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('api_logs.index') }}">API Logs</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <!-- If session-based auth is restored, put profile dropdown here -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script>
        // SweetAlert Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Global AJAX Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Loading Spinner
        function showLoading() {
            $('.loading-spinner').show();
        }

        function hideLoading() {
            $('.loading-spinner').hide();
        }

        // DataTables Configuration
        function initializeDataTable(selector, options = {}) {
            const defaultOptions = {
                responsive: true,
                language: {
                    search: '<i class="fas fa-search"></i>',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
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
                order: [[0, 'desc']]
            };

            return $(selector).DataTable({...defaultOptions, ...options});
        }

        // Delete Confirmation
        function confirmDelete(url, title = 'Are you sure?', text = 'This action cannot be undone.') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            hideLoading();
                            Toast.fire({
                                icon: 'success',
                                title: 'Deleted successfully!'
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            hideLoading();
                            Toast.fire({
                                icon: 'error',
                                title: 'Error deleting item'
                            });
                        }
                    });
                }
            });
        }

        // Status Update
        function updateStatus(url, status) {
            showLoading();
            $.ajax({
                url: url,
                type: 'PATCH',
                data: { status: status },
                success: function(response) {
                    hideLoading();
                    Toast.fire({
                        icon: 'success',
                        title: 'Status updated successfully!'
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    hideLoading();
                    Toast.fire({
                        icon: 'error',
                        title: 'Error updating status'
                    });
                }
            });
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                let submitted = false;
                form.addEventListener('submit', function(e) {
                    if (submitted) {
                        e.preventDefault();
                        return false;
                    }
                    submitted = true;
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) btn.disabled = true;
                });
            });
        });

        // GLOBAL NETWORK LOGGER
        (function() {
            // Log all jQuery AJAX requests
            if (window.jQuery) {
                $(document).ajaxSend(function(event, jqxhr, settings) {
                    console.log('[AJAX]', settings.type, settings.url, 'Data:', settings.data, '\nCall stack:', new Error().stack);
                });
            }
            // Log all fetch requests
            if (window.fetch) {
                const origFetch = window.fetch;
                window.fetch = function() {
                    const args = arguments;
                    let url = args[0];
                    let opts = args[1] || {};
                    console.log('[FETCH]', opts.method || 'GET', url, 'Opts:', opts, '\nCall stack:', new Error().stack);
                    return origFetch.apply(this, arguments);
                };
            }
            // Log all XMLHttpRequests
            const origOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url) {
                this.addEventListener('loadstart', function() {
                    console.log('[XHR]', method, url, '\nCall stack:', new Error().stack);
                });
                origOpen.apply(this, arguments);
            };
        })();
    </script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')
</body>
</html>
