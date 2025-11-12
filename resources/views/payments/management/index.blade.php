@extends('layouts.app')

@section('title', 'Payment Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Payment Management</h3>
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
            <a href="#">Payment Management</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Order ID, customer name or email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="settlement" {{ request('status') == 'settlement' ? 'selected' : '' }}>Settlement</option>
                            <option value="expire" {{ request('status') == 'expire' ? 'selected' : '' }}>Expired</option>
                            <option value="cancel" {{ request('status') == 'cancel' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                            <i class="fa fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Transactions ({{ $transactions->total() }})</h4>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <strong>{{ $transaction->order_id }}</strong>
                                    @if($transaction->transaction_id)
                                    <br>
                                    <small class="text-muted">{{ $transaction->transaction_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $transaction->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $transaction->user->email }}</small>
                                </td>
                                <td>
                                    @if($transaction->payment_type)
                                        <span class="badge badge-secondary">{{ strtoupper($transaction->payment_type) }}</span>
                                        @if($transaction->bank)
                                        <br><small>{{ strtoupper($transaction->bank) }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <small>
                                        {{ $transaction->created_at->format('d M Y') }}
                                        <br>
                                        {{ $transaction->created_at->format('H:i') }}
                                    </small>
                                    @if($transaction->paid_at)
                                    <br>
                                    <small class="text-success">
                                        <i class="fa fa-check"></i> {{ $transaction->paid_at->format('d M Y, H:i') }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payments.show', $transaction) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fa fa-credit-card fa-5x text-muted mb-3"></i>
                    <h4>No transactions found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
