<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Restaurant Management System') }}</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background-color: #f4f6f9;
        }

        .sidebar {
            min-height: 100vh;
            width: 250px;
            background-color: #343a40;
        }

        .sidebar .nav-link {
            color: #c2c7d0;
            font-size: 0.95rem;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 2px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .sidebar .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .content-area {
            flex-grow: 1;
        }

        .avatar-img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
    @stack('styles')
</head>

<body class="d-flex">

    <!-- Sidebar Navigation -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white">
        <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="bi bi-egg-fried fs-4 me-2 text-warning"></i>
            <span class="fs-5 fw-bold">RestoManage</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            @php $role = auth()->user()->role->value; @endphp

            @if ($role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Staff Members
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.sections.index') }}"
                        class="nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Menu Sections
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                        class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.subcategories.index') }}"
                        class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-2"></i> Subcategories
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.menu-items.index') }}"
                        class="nav-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}">
                        <i class="bi bi-card-list"></i> Menu Items
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-grid-3x3-gap"></i> Tables
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                </li>
            @elseif($role === 'waiter')
                <li class="nav-item">
                    <a href="{{ route('waiter.dashboard') }}"
                        class="nav-link {{ request()->routeIs('waiter.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-plus-circle"></i> New Order
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-grid-3x3"></i> Tables View
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-calendar-check"></i> Reservations
                    </a>
                </li>
            @elseif($role === 'cashier')
                <li class="nav-item">
                    <a href="{{ route('cashier.dashboard') }}"
                        class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-secondary">
                        <i class="bi bi-receipt"></i> Orders for Billing
                    </a>
                </li>
            @elseif($role === 'kitchen_staff')
                <li class="nav-item">
                    <a href="{{ route('kitchen.view') }}"
                        class="nav-link {{ request()->routeIs('kitchen.view') ? 'active' : '' }}">
                        <i class="bi bi-fire"></i> Kitchen Display
                    </a>
                </li>
            @endif

            <hr class="text-secondary my-2">

            <!-- رابط استعراض المنيو العام (يظهر للجميع) -->
            <li>
                <a href="{{ route('menu.display') }}"
                    class="nav-link {{ request()->routeIs('menu.display') ? 'active' : '' }}">
                    <i class="bi bi-book-half"></i> Browse Menu
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown">
                <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                    class="avatar-img me-2" alt="Avatar">
                <strong>{{ auth()->user()->name }}</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My
                        Profile</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i
                                class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="content-area d-flex flex-column">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4">
            <div class="container-fluid p-0">
                <span class="navbar-brand fw-semibold text-secondary fs-6">@yield('page_title', 'Dashboard')</span>
                <div class="d-flex align-items-center gap-3">
                    <span
                        class="badge bg-primary text-uppercase">{{ str_replace('_', ' ', auth()->user()->role->value) }}</span>
                </div>
            </div>
        </nav>

        <!-- Dynamic Body Content -->
        <main class="p-4 flex-grow-1">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Profile updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Password updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
