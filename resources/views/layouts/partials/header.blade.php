<div class="main-header">
    <div class="main-header-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="white">
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ asset('storage/20251217_225404.png') }}" alt="navbar brand" class="navbar-brand" style="height: 40px; width: auto; object-fit: contain;" />
                <span class="logo-text">Tirta Kesuma</span>
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
    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-user dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        <div class="avatar-sm">
                            <img src="{{ asset('template/assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle" />
                        </div>
                        <span class="profile-username">
                            <span class="op-7">Hi,</span>
                            <span class="fw-bold">{{ Auth::user()->name }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        <img src="{{ asset('template/assets/img/profile.jpg') }}" alt="image profile" class="avatar-img rounded" />
                                    </div>
                                    <div class="u-text">
                                        <h4>{{ Auth::user()->name }}</h4>
                                        <p class="text-muted">{{ Auth::user()->email }}</p>
                                        @if(Auth::user()->roles->isNotEmpty())
                                            <span class="badge badge-primary">{{ Auth::user()->roles->first()->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('profile.index') }}" class="dropdown-item">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); confirmAction('Are you sure you want to logout?', function() { document.getElementById('logoutForm').submit(); });">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </form>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>
