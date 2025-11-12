@extends('layouts.app')

@section('title', 'Transaction Detail - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Transaction Detail</h3>
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
            <a href="{{ route('payments.index') }}">Payment Management</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">{{ $transaction->order_id }}</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Transaction Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Transaction Information</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Order ID:</strong>
                        <p>{{ $transaction->order_id }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Transaction ID:</strong>
                        <p>{{ $transaction->transaction_id ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p>
                            @if($transaction->status === 'settlement')
                                <span class="badge badge-success">Settlement</span>
                            @elseif($transaction->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($transaction->status === 'expire')
                                <span class="badge badge-secondary">Expired</span>
                            @elseif($transaction->status === 'cancel')
                                <span class="badge badge-danger">Cancelled</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Amount:</strong>
                        <p class="text-primary"><h4>Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</h4></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Payment Type:</strong>
                        <p>{{ $transaction->payment_type ? strtoupper($transaction->payment_type) : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Method:</strong>
                        <p>{{ $transaction->payment_method ?? '-' }}</p>
                    </div>
                </div>

                @if($transaction->bank || $transaction->va_number)
                <div class="row mb-3">
                    @if($transaction->bank)
                    <div class="col-md-6">
                        <strong>Bank:</strong>
                        <p>{{ strtoupper($transaction->bank) }}</p>
                    </div>
                    @endif
                    @if($transaction->va_number)
                    <div class="col-md-6">
                        <strong>VA Number:</strong>
                        <p><code>{{ $transaction->va_number }}</code></p>
                    </div>
                    @endif
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Created At:</strong>
                        <p>{{ $transaction->created_at->format('d M Y, H:i:s') }}</p>
                    </div>
                    @if($transaction->paid_at)
                    <div class="col-md-6">
                        <strong>Paid At:</strong>
                        <p class="text-success">
                            <i class="fa fa-check-circle"></i>
                            {{ $transaction->paid_at->format('d M Y, H:i:s') }}
                        </p>
                    </div>
                    @endif
                </div>
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
                        <p>{{ $transaction->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p>{{ $transaction->user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rental Information -->
        @if($transaction->rental)
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Related Rental</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Rental Code:</strong>
                        <p>
                            <a href="{{ route('rentals.show', $transaction->rental) }}">
                                {{ $transaction->rental->rental_code }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Rental Status:</strong>
                        <p>
                            @if($transaction->rental->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($transaction->rental->status === 'confirmed')
                                <span class="badge badge-info">Confirmed</span>
                            @elseif($transaction->rental->status === 'on_rent')
                                <span class="badge badge-primary">On Rent</span>
                            @elseif($transaction->rental->status === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @else
                                <span class="badge badge-secondary">Cancelled</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <strong>Items:</strong>
                        <ul class="list-unstyled mt-2">
                            @foreach($transaction->rental->rentalItems as $item)
                            <li class="mb-1">
                                <i class="fa fa-box text-primary"></i>
                                {{ $item->item->name }} ({{ $item->quantity }}x)
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Payment Summary -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Payment Summary</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total Amount:</strong>
                    <strong class="text-primary">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</strong>
                </div>

                @if($transaction->status === 'settlement')
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> Payment Successful
                </div>
                @elseif($transaction->status === 'pending')
                <div class="alert alert-warning">
                    <i class="fa fa-clock"></i> Waiting for payment
                </div>
                @else
                <div class="alert alert-secondary">
                    <i class="fa fa-times-circle"></i> Payment {{ ucfirst($transaction->status) }}
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
                <a href="{{ route('payments.index') }}" class="btn btn-secondary w-100">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
