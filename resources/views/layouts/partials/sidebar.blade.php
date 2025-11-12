<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ asset('template/assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                @can('view-dashboard-privilege')
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @else
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a href="{{ route('home') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @endcan

                @canany(['view-items', 'view-catalog', 'view-rentals'])
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Menu</h4>
                </li>
                @endcanany

                @can('view-catalog')
                <li class="nav-item {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
                    <a href="{{ route('catalog.index') }}">
                        <i class="fas fa-shopping-bag"></i>
                        <p>Catalog</p>
                    </a>
                </li>
                @endcan

                @can('view-catalog')
                <li class="nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                    <a href="{{ route('cart.index') }}">
                        <i class="fas fa-shopping-cart"></i>
                        <p>My Cart</p>
                    </a>
                </li>
                @endcan

                @can('view-items')
                <li class="nav-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <a href="{{ route('items.index') }}">
                        <i class="fas fa-box"></i>
                        <p>Items Management</p>
                    </a>
                </li>
                @endcan

                {{-- My Rentals - For regular users only --}}
                @can('view-rentals')
                @cannot('manage-all-rentals')
                <li class="nav-item {{ request()->routeIs('checkout.*') ? 'active' : '' }}">
                    <a href="{{ route('checkout.history') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>My Rentals</p>
                    </a>
                </li>
                @endcannot
                @endcan

                {{-- Transaction Management - For privileged users only (includes Rentals & Payments) --}}
                @canany(['manage-all-rentals', 'manage-all-payments'])
                <li class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Transaction Management</p>
                    </a>
                </li>
                @endcanany

                @canany(['view-categories', 'view-users', 'view-roles', 'view-permissions'])
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Management</h4>
                </li>
                @endcanany

                @can('view-categories')
                <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}">
                        <i class="fas fa-tags"></i>
                        <p>Categories</p>
                    </a>
                </li>
                @endcan

                @can('view-users')
                <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fas fa-users"></i>
                        <p>Users</p>
                    </a>
                </li>
                @endcan

                @can('view-roles')
                <li class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}">
                        <i class="fas fa-user-tag"></i>
                        <p>Roles</p>
                    </a>
                </li>
                @endcan

                @can('view-permissions')
                <li class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('permissions.index') }}">
                        <i class="fas fa-shield-alt"></i>
                        <p>Permissions</p>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </div>
</div>
