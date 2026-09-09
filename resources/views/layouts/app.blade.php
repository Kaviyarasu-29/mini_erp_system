<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Mini ERP System') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #334155;
        }

        .navbar-custom {
            background-color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            min-height: calc(100vh - 64px);
        }

        .sidebar .nav-link {
            color: #64748b;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #0284c7;
            background-color: #f0f9ff;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar-heading {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 1rem 1rem 0.5rem;
        }

        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            border-top-left-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
        }

        .badge-status-active {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-status-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .table-custom td {
            vertical-align: middle;
        }

        /* Pagination Styling & SVG Constraint */
        .pagination svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }

        .page-item.active .page-link {
            background-color: #0284c7;
            border-color: #0284c7;
        }

        .page-link {
            color: #475569;
        }

        .page-link:hover {
            color: #0284c7;
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('masters.products.index') }}">
                <i class="bi bi-box-seam-fill text-info fs-4"></i>
                <span>Mini ERP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="topNavbar">
                <ul class="navbar-header ms-auto navbar-nav align-items-center gap-3">
                    <li class="nav-item">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                            <i class="bi bi-clock me-1"></i> {{ now()->format('d M Y') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
                <div class="sidebar-heading">Transactions</div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                        <i class="bi bi-cart-plus"></i> Purchases
                    </a>
                    <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                        <i class="bi bi-bag-check"></i> Sales
                    </a>
                </nav>

                <div class="sidebar-heading">Masters</div>
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('masters.categories.*') ? 'active' : '' }}" href="{{ route('masters.categories.index') }}">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                    <a class="nav-link {{ request()->routeIs('masters.units.*') ? 'active' : '' }}" href="{{ route('masters.units.index') }}">
                        <i class="bi bi-rulers"></i> Units
                    </a>
                    <a class="nav-link {{ request()->routeIs('masters.taxes.*') ? 'active' : '' }}" href="{{ route('masters.taxes.index') }}">
                        <i class="bi bi-percent"></i> Taxes
                    </a>
                    <a class="nav-link {{ request()->routeIs('masters.customers.*') ? 'active' : '' }}" href="{{ route('masters.customers.index') }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                    <a class="nav-link {{ request()->routeIs('masters.suppliers.*') ? 'active' : '' }}" href="{{ route('masters.suppliers.index') }}">
                        <i class="bi bi-truck"></i> Suppliers
                    </a>
                    <a class="nav-link {{ request()->routeIs('masters.products.*') ? 'active' : '' }}" href="{{ route('masters.products.index') }}">
                        <i class="bi bi-boxes"></i> Products
                    </a>
                </nav>
            </div>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i> Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-0 text-muted" id="deleteModalMessage">Are you sure you want to delete this record? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(actionUrl, itemName = 'this item') {
            const deleteForm = document.getElementById('deleteForm');
            const messageEl = document.getElementById('deleteModalMessage');
            deleteForm.action = actionUrl;
            messageEl.textContent = `Are you sure you want to delete "${itemName}"? This action cannot be undone.`;
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        }
    </script>
    @stack('scripts')
</body>
</html>
