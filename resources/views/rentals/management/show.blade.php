@extends('layouts.app')

@section('title', 'Rental Detail - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Rental Detail</h3>
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
            <a href="{{ route('rentals.index') }}">Rental Management</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">{{ $rental->rental_code }}</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Rental Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Rental Information</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Rental Code:</strong>
                        <p>{{ $rental->rental_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Created At:</strong>
                        <p>{{ $rental->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p>
                            @if($rental->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($rental->status === 'confirmed')
                                <span class="badge badge-info">Confirmed</span>
                            @elseif($rental->status === 'on_rent')
                                <span class="badge badge-primary">On Rent</span>
                            @elseif($rental->status === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @else
                                <span class="badge badge-secondary">Cancelled</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Status:</strong>
                        <p>
                            @if($rental->payment_status === 'paid')
                                <span class="badge badge-success">Paid</span>
                            @else
                                <span class="badge badge-danger">Unpaid</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Rental Period:</strong>
                        <p>
                            {{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}
                            <br>
                            <span class="badge badge-info">{{ $rental->duration_days }} days</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        @if($rental->confirmed_at)
                        <strong>Confirmed At:</strong>
                        <p>{{ $rental->confirmed_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>

                @if($rental->notes)
                <div class="row">
                    <div class="col-md-12">
                        <strong>Notes:</strong>
                        <p>{{ $rental->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Customer Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Name:</strong>
                        <p>{{ $rental->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $rental->user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rental Items -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Rental Items</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Price/Period</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rental->rentalItems as $rentalItem)
                            <tr>
                                <td>{{ $rentalItem->item->name }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $rentalItem->item->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td>{{ $rentalItem->quantity }}</td>
                                <td>Rp {{ number_format($rentalItem->price_per_day, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($rentalItem->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Payment Summary -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Payment Summary</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong>Rp {{ number_format($rental->subtotal, 0, ',', '.') }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong>
                </div>

                @if($rental->transaction)
                <div class="alert alert-info">
                    <small>
                        <strong>Order ID:</strong><br>
                        {{ $rental->transaction->order_id }}
                    </small>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                @if($rental->status === 'confirmed' || $rental->status === 'on_rent')
                <form action="{{ route('rentals.update.status', $rental) }}" method="POST" class="mb-2">
                    @csrf
                    <input type="hidden" name="status" value="on_rent">
                    <button type="submit" class="btn btn-primary w-100 mb-2" {{ $rental->status === 'on_rent' ? 'disabled' : '' }}>
                        <i class="fa fa-truck"></i> Mark as On Rent
                    </button>
                </form>

                <form action="{{ route('rentals.update.status', $rental) }}" method="POST" onsubmit="return confirm('Mark as returned? Stock will be increased automatically.')">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-check"></i> Mark as Returned
                    </button>
                </form>
                @elseif($rental->status === 'completed')
                <div class="alert alert-success mb-0">
                    <i class="fa fa-check-circle"></i> Rental Completed
                    @if($rental->returned_at)
                    <br><small>Returned at: {{ $rental->returned_at->format('d M Y, H:i') }}</small>
                    @endif
                </div>
                @elseif($rental->status === 'pending')
                <div class="alert alert-warning mb-0">
                    <i class="fa fa-clock"></i> Waiting for payment
                </div>
                @else
                <div class="alert alert-secondary mb-0">
                    <i class="fa fa-times-circle"></i> Rental Cancelled
                </div>
                @endif

                <a href="{{ route('rentals.index') }}" class="btn btn-secondary w-100 mt-3">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
