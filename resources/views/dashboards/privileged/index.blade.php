@extends('layouts.app')

@section('title', 'Dashboard - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Management Dashboard</h3>
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

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ $stats['total_users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Items</div>
                <div class="stat-value">{{ $stats['total_items'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Rentals</div>
                <div class="stat-value">{{ $stats['active_rentals'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-secondary">
            <div class="stat-secondary-label">Pending Rentals</div>
            <div class="stat-secondary-value">{{ $stats['pending_rentals'] }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-secondary">
            <div class="stat-secondary-label">Completed Rentals</div>
            <div class="stat-secondary-value">{{ $stats['completed_rentals'] }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-secondary">
            <div class="stat-secondary-label">Pending Payments</div>
            <div class="stat-secondary-value">{{ $stats['pending_payments'] }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-secondary alert-card">
            <div class="stat-secondary-label">Low Stock Items</div>
            <div class="stat-secondary-value">{{ $stats['low_stock_items'] }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent Rentals -->
    <div class="col-lg-8">
        <div class="data-card">
            <div class="data-card-header">
                <h5 class="data-card-title">Recent Rentals</h5>
                <a href="{{ route('transactions.index') }}" class="btn-view-all">View All</a>
            </div>
            <div class="data-card-body">
                @if($recentRentals->count() > 0)
                <div class="table-responsive">
                    <table class="table-minimal">
                        <thead>
                            <tr>
                                <th>Rental Code</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRentals as $rental)
                            <tr>
                                <td>
                                    <span class="text-code">{{ $rental->rental_code }}</span>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $rental->user->name }}</span>
                                        <span class="customer-email">{{ $rental->user->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($rental->rentalItems->count() > 0)
                                        <span class="item-name">{{ $rental->rentalItems->first()->item->name }}</span>
                                        @if($rental->rentalItems->count() > 1)
                                            <span class="item-count">+{{ $rental->rentalItems->count() - 1 }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="date-text">{{ $rental->start_date->format('d M') }} - {{ $rental->end_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    @if($rental->status === 'pending')
                                        <span class="badge-status badge-pending">Pending</span>
                                    @elseif($rental->status === 'confirmed')
                                        <span class="badge-status badge-confirmed">Confirmed</span>
                                    @elseif($rental->status === 'on_rent')
                                        <span class="badge-status badge-active">Active</span>
                                    @else
                                        <span class="badge-status badge-completed">Completed</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="price-text">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state-minimal">
                    <i class="fas fa-clipboard-list"></i>
                    <p>No recent rentals</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-4">
        <div class="data-card">
            <div class="data-card-header">
                <h5 class="data-card-title">Low Stock Alert</h5>
            </div>
            <div class="data-card-body">
                @if($lowStockItems->count() > 0)
                <div class="alert-list">
                    @foreach($lowStockItems as $item)
                    <div class="alert-item">
                        <div class="alert-item-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-item-content">
                            <div class="alert-item-name">{{ $item->name }}</div>
                            <div class="alert-item-category">{{ $item->category?->name ?? '-' }}</div>
                        </div>
                        <div class="alert-item-stock">
                            <span class="stock-value">{{ $item->available_stock }}/{{ $item->stock }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state-minimal">
                    <i class="fas fa-check-circle"></i>
                    <p>All items have sufficient stock</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Stat Cards */
.stat-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.stat-icon {
    width: 56px;
    height: 56px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon i {
    font-size: 1.5rem;
    color: #6c757d;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}

/* Secondary Stat Cards */
.stat-card-secondary {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-secondary-label {
    font-size: 0.8125rem;
    color: #6c757d;
}

.stat-secondary-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #495057;
}

.alert-card {
    background: #fff3cd;
    border-color: #ffe69c;
}

.alert-card .stat-secondary-value {
    color: #856404;
}

/* Data Cards */
.data-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

.data-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.data-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.btn-view-all {
    font-size: 0.875rem;
    color: #177dff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.btn-view-all:hover {
    color: #0c5fd7;
}

.data-card-body {
    padding: 1.5rem;
}

/* Minimal Table */
.table-minimal {
    width: 100%;
    font-size: 0.875rem;
}

.table-minimal thead th {
    padding: 0.75rem 0.5rem;
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.table-minimal tbody td {
    padding: 0.875rem 0.5rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

.table-minimal tbody tr:last-child td {
    border-bottom: none;
}

.text-code {
    font-family: monospace;
    font-size: 0.8125rem;
    color: #495057;
    font-weight: 600;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-name {
    font-weight: 500;
    color: #212529;
}

.customer-email {
    font-size: 0.75rem;
    color: #6c757d;
}

.item-name {
    color: #495057;
}

.item-count {
    font-size: 0.75rem;
    color: #6c757d;
    margin-left: 0.25rem;
}

.date-text {
    font-size: 0.8125rem;
    color: #6c757d;
}

.badge-status {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-pending {
    background: #fff3cd;
    color: #856404;
}

.badge-confirmed {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-completed {
    background: #e9ecef;
    color: #495057;
}

.price-text {
    font-weight: 600;
    color: #212529;
}

/* Alert List */
.alert-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem;
    background: #fff3cd;
    border: 1px solid #ffe69c;
    border-radius: 8px;
}

.alert-item-icon {
    width: 36px;
    height: 36px;
    background: #fff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.alert-item-icon i {
    color: #ffc107;
    font-size: 1rem;
}

.alert-item-content {
    flex: 1;
}

.alert-item-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #212529;
}

.alert-item-category {
    font-size: 0.75rem;
    color: #6c757d;
}

.alert-item-stock {
    flex-shrink: 0;
}

.stock-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: #856404;
}

/* Empty State */
.empty-state-minimal {
    text-align: center;
    padding: 2rem;
}

.empty-state-minimal i {
    font-size: 2.5rem;
    color: #dee2e6;
    margin-bottom: 0.75rem;
}

.empty-state-minimal p {
    color: #6c757d;
    margin: 0;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-value {
        font-size: 1.5rem;
    }

    .data-card-body {
        padding: 1rem;
    }

    .table-minimal {
        font-size: 0.8125rem;
    }
}
</style>
@endpush
