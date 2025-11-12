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
<div class="row">
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Total Rentals</p>
                            <h4 class="card-title">{{ $totalRentals }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Dikonfirmasi</p>
                            <h4 class="card-title">{{ $confirmedRentals }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Berlangsung</p>
                            <h4 class="card-title">{{ $onRentRentals }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Selesai</p>
                            <h4 class="card-title">{{ $completedRentals }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('view-catalog')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-shopping-bag"></i><br>
                            Browse Catalog
                        </a>
                    </div>
                    @endcan
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('cart.index') }}" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-shopping-cart"></i><br>
                            My Cart
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('profile.index') }}" class="btn btn-info btn-lg btn-block">
                            <i class="fas fa-user"></i><br>
                            My Profile
                        </a>
                    </div>
                    @can('view-rentals')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('checkout.history') }}" class="btn btn-warning btn-lg btn-block">
                            <i class="fas fa-clipboard-list"></i><br>
                            My Rentals
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
