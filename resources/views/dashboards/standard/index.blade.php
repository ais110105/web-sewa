@extends('layouts.app')

@section('title', 'Dashboard - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Welcome, {{ Auth::user()->name }}!</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('home') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Dashboard</a>
        </li>
    </ul>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="stat-card-minimal">
            <div class="stat-icon-minimal">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content-minimal">
                <div class="stat-label-minimal">Total Rentals</div>
                <div class="stat-value-minimal">{{ $totalRentals }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card-minimal">
            <div class="stat-icon-minimal">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content-minimal">
                <div class="stat-label-minimal">Dikonfirmasi</div>
                <div class="stat-value-minimal">{{ $confirmedRentals }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card-minimal">
            <div class="stat-icon-minimal">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content-minimal">
                <div class="stat-label-minimal">Berlangsung</div>
                <div class="stat-value-minimal">{{ $onRentRentals }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="stat-card-minimal">
            <div class="stat-icon-minimal">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-content-minimal">
                <div class="stat-label-minimal">Selesai</div>
                <div class="stat-value-minimal">{{ $completedRentals }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions-section">
    <h5 class="section-title-minimal">Quick Actions</h5>
    <div class="row g-3">
        @can('view-catalog')
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('catalog.index') }}" class="action-card-minimal">
                <div class="action-icon-minimal">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="action-label-minimal">Browse Catalog</div>
            </a>
        </div>
        @endcan
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('cart.index') }}" class="action-card-minimal">
                <div class="action-icon-minimal">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="action-label-minimal">My Cart</div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('profile.index') }}" class="action-card-minimal">
                <div class="action-icon-minimal">
                    <i class="fas fa-user"></i>
                </div>
                <div class="action-label-minimal">My Profile</div>
            </a>
        </div>
        @can('view-rentals')
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('checkout.history') }}" class="action-card-minimal">
                <div class="action-icon-minimal">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="action-label-minimal">My Rentals</div>
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection

@push('styles')
<style>
/* Stat Cards Minimal */
.stat-card-minimal {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card-minimal:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}

.stat-icon-minimal {
    width: 56px;
    height: 56px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-minimal i {
    font-size: 1.5rem;
    color: #6c757d;
}

.stat-content-minimal {
    flex: 1;
}

.stat-label-minimal {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.stat-value-minimal {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}

/* Quick Actions Section */
.quick-actions-section {
    margin-top: 2rem;
}

.section-title-minimal {
    font-size: 1.125rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1.25rem;
}

.action-card-minimal {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 2rem 1.5rem;
    text-decoration: none;
    transition: all 0.2s;
    min-height: 160px;
}

.action-card-minimal:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    transform: translateY(-4px);
    border-color: #177dff;
}

.action-icon-minimal {
    width: 64px;
    height: 64px;
    background: #f8f9fa;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    transition: all 0.2s;
}

.action-card-minimal:hover .action-icon-minimal {
    background: #177dff;
}

.action-icon-minimal i {
    font-size: 1.75rem;
    color: #6c757d;
    transition: color 0.2s;
}

.action-card-minimal:hover .action-icon-minimal i {
    color: #ffffff;
}

.action-label-minimal {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    text-align: center;
    transition: color 0.2s;
}

.action-card-minimal:hover .action-label-minimal {
    color: #177dff;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-value-minimal {
        font-size: 1.5rem;
    }

    .action-card-minimal {
        min-height: 140px;
        padding: 1.5rem 1rem;
    }

    .action-icon-minimal {
        width: 56px;
        height: 56px;
    }

    .action-icon-minimal i {
        font-size: 1.5rem;
    }
}
</style>
@endpush
